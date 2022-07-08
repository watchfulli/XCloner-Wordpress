/** global: XCLONER_AJAXURL */
/** global: Materialize */
import {getUrlParam} from './xcloner-admin.js';

var backup_cotent_modal;
var backup_encryption_modal;
var backup_decryption_modal;
var remote_storage_modal;
var local_storage_upload_modal;

document.addEventListener("DOMContentLoaded", function () {
  var Modalelem = document.querySelector("#backup_cotent_modal");
  backup_cotent_modal = M.Modal.init(Modalelem);

  var Modalelem = document.querySelector("#backup_encryption_modal");
  backup_encryption_modal = M.Modal.init(Modalelem);

  var Modalelem = document.querySelector("#backup_decryption_modal");
  backup_decryption_modal = M.Modal.init(Modalelem);

  var Modalelem = document.querySelector("#remote_storage_modal");
  remote_storage_modal = M.Modal.init(Modalelem);
  
  var Modalelem = document.querySelector("#local_storage_upload_modal");
  local_storage_upload_modal = M.Modal.init(Modalelem);
  
});

export class Xcloner_Manage_Backups {
  constructor() {
    this.file_counter = 0;
    this.storage_selection = "";
    this.dataTable = "";
    //this.edit_modal = jQuery('.modal').modal();
  }

  download_backup_by_name(id) {
    window.open(XCLONER_AJAXURL + "&action=download_backup_by_name&name=" + id+"&storage_selection="+this.storage_selection);
    return false;
  }

  delete_backup_by_name(id, elem, dataTable) {
    var $this = this;

    if (id) {
      jQuery.ajax({
        url: XCLONER_AJAXURL,
        method: "post",
        data: {
          action: "delete_backup_by_name",
          name: id,
          storage_selection: this.storage_selection,
        },
        success: function (response) {
          if (response.finished) {
            dataTable.row(jQuery(elem).parents("tr")).remove().draw();
          } else {
            alert("There was an error deleting the file");
          }
        },
        dataType: "json",
      });
    }
  }

  list_backup_content_callback(backup_file, start = 0, part = 0) {
    var $this = this;

    if (backup_file) {
      jQuery.ajax({
        url: XCLONER_AJAXURL,
        method: "post",
        data: {
          action: "list_backup_files",
          file: backup_file,
          start: start,
          part: part,
        },
        success: function (response) {
          if (response.error) {
            jQuery("#backup_cotent_modal .files-list")
              .addClass("error")
              .prepend(response.message);
            jQuery("#backup_cotent_modal .progress > div")
              .addClass("determinate")
              .removeClass(".indeterminate")
              .css("width", "100%");
            return;
          }

          var files_text = [];

          for (var i in response.files) {
            if (response.total_size !== undefined) {
              var percent =
                parseInt(response.start * 100) / parseInt(response.total_size);
              //jQuery("#backup_cotent_modal .progress .determinate").css('width', percent + "%")
            }

            $this.file_counter++;

            files_text[i] =
              "<li>" +
              ($this.file_counter +
                ". <span title='" +
                response.files[i].mtime +
                "'>" +
                response.files[i].path +
                "</span> (" +
                response.files[i].size +
                " bytes)") +
              "</li>";
          }

          jQuery("#backup_cotent_modal .modal-content .files-list").prepend(
            files_text.reverse().join("\n")
          );

          if (
            !response.finished &&
            jQuery("#backup_cotent_modal").is(":visible")
          ) {
            $this.list_backup_content_callback(
              backup_file,
              response.start,
              response.part
            );
          } else {
            jQuery("#backup_cotent_modal .progress > div")
              .addClass("determinate")
              .removeClass(".indeterminate")
              .css("width", "100%");
          }
        },
        error: function (xhr, textStatus, error) {
          jQuery("#backup_cotent_modal .files-list")
            .addClass("error")
            .prepend(textStatus + error);
        },
        dataType: "json",
      });
    }
  }

  list_backup_content(backup_file) {
    this.file_counter = 0;
    jQuery("#backup_cotent_modal .modal-content .files-list")
      .text("")
      .removeClass("error");
    jQuery("#backup_cotent_modal .modal-content .backup-name").text(
      backup_file
    );
    backup_cotent_modal.open();
    jQuery("#backup_cotent_modal .progress > div")
      .removeClass("determinate")
      .addClass("indeterminate");

    this.list_backup_content_callback(backup_file);
  }

  backup_encryption_callback(backup_file, start = 0, part = 0, iv = 0) {
    var $this = this;

    if (backup_file) {
      jQuery.ajax({
        url: XCLONER_AJAXURL,
        method: "post",
        data: {
          action: "backup_encryption",
          file: backup_file,
          start: start,
          part: part,
          iv: iv,
        },
        success: function (response) {
          if (response.total_size !== undefined) {
            jQuery("#backup_encryption_modal .progress > div")
              .removeClass("indeterminate")
              .addClass("determinate");
            var percent =
              parseInt(response.start * 100) / parseInt(response.total_size);
            jQuery("#backup_encryption_modal .progress .determinate").css(
              "width",
              parseInt(percent) + "%"
            );
            jQuery("#backup_encryption_modal .modal-content .files-list").text(
              "Encrypting " +
                response.processing_file +
                " " +
                parseInt(percent) +
                "%"
            );
          }

          if (response.error) {
            jQuery("#backup_encryption_modal .notice").show();
            jQuery("#backup_encryption_modal .files-list")
              .addClass("error")
              .prepend(response.message + " ");
            jQuery("#backup_encryption_modal .progress > div")
              .addClass("determinate")
              .removeClass("indeterminate")
              .css("width", "100%");
            return;
          }

          if (
            !response.finished &&
            jQuery("#backup_encryption_modal").is(":visible")
          ) {
            $this.backup_encryption_callback(
              backup_file,
              response.start,
              response.part,
              response.iv
            );
          } else {
            jQuery("#backup_encryption_modal .progress > div")
              .addClass("determinate")
              .removeClass("indeterminate")
              .css("width", "100%");
            jQuery("#backup_encryption_modal .modal-content .files-list").text(
              "Done Encrypting."
            );
            dataTable.ajax.reload();
          }
        },
        error: function (xhr, textStatus, error) {
          jQuery("#backup_encryption_modal .files-list")
            .addClass("error")
            .prepend(textStatus + error);
        },
        dataType: "json",
      });
    }
  }

  backup_encryption(backup_file, start = 0) {
    this.file_counter = 0;

    jQuery("#backup_encryption_modal .modal-content .backup-name").text(
      backup_file
    );
    backup_encryption_modal.open();
    jQuery("#backup_encryption_modal .progress > div");
    jQuery("#backup_encryption_modal .notice").show();

    jQuery("#backup_encryption_modal a.btn").attr(
      "onclick",
      "xcloner_manage_backups.backup_encryption('" +
        backup_file +
        "', true)"
    );
    jQuery("#backup_encryption_modal .modal-content .files-list")
      .text("")
      .removeClass("error");

    if (start) {
      jQuery("#backup_encryption_modal .notice").hide();
      this.backup_encryption_callback(backup_file);
    }
  }

  backup_decryption_callback(backup_file, start = 0, part = 0, iv = 0) {
    var $this = this;

    var decryption_key = jQuery(
      "#backup_decryption_modal #decryption_key"
    ).val();

    if (backup_file) {
      jQuery.ajax({
        url: XCLONER_AJAXURL,
        method: "post",
        data: {
          action: "backup_decryption",
          file: backup_file,
          start: start,
          part: part,
          iv: iv,
          decryption_key: decryption_key,
        },
        success: function (response) {
          if (!response.start) {
            response.start = 0;
          }
          if (response.total_size !== undefined) {
            jQuery("#backup_decryption_modal .progress > div")
              .removeClass("indeterminate")
              .addClass("determinate");
            var percent =
              parseInt(response.start * 100) / parseInt(response.total_size);
            jQuery("#backup_decryption_modal .progress .determinate").css(
              "width",
              parseInt(percent) + "%"
            );
            jQuery("#backup_decryption_modal .modal-content .files-list").text(
              "Decrypting " +
                response.processing_file +
                " " +
                parseInt(percent) +
                "%"
            );
          }

          if (response.error) {
            jQuery("#backup_decryption_modal .files-list")
              .addClass("error")
              .prepend(response.message + " ");
            jQuery("#backup_decryption_modal .progress > div")
              .addClass("determinate")
              .removeClass("indeterminate")
              .css("width", "100%");
            jQuery("#backup_decryption_modal .notice").show();
            return;
          }

          if (
            !response.finished &&
            jQuery("#backup_decryption_modal").is(":visible")
          ) {
            $this.backup_decryption_callback(
              backup_file,
              response.start,
              response.part,
              response.iv
            );
          } else {
            jQuery("#backup_decryption_modal .progress > div")
              .addClass("determinate")
              .removeClass("indeterminate")
              .css("width", "100%");
            jQuery("#backup_decryption_modal .modal-content .files-list").text(
              "Done Decrypting."
            );
            dataTable.ajax.reload();
          }
        },
        error: function (xhr, textStatus, error) {
          jQuery("#backup_decryption_modal .files-list")
            .addClass("error")
            .prepend(textStatus + error);
        },
        dataType: "json",
      });
    }
  }

  backup_decryption(backup_file, start = 0) {
    this.file_counter = 0;

    jQuery("#backup_decryption_modal .modal-content .backup-name").text(
      backup_file
    );
    backup_decryption_modal.open();
    jQuery("#backup_decryption_modal .progress > div");
    jQuery("#backup_decryption_modal .notice").show();

    jQuery("#backup_decryption_modal a.btn").attr(
      "onclick",
      "xcloner_manage_backups.backup_decryption('" +
        backup_file +
        "', true)"
    );
    jQuery("#backup_decryption_modal .modal-content .files-list")
      .text("")
      .removeClass("error");

    if (start) {
      jQuery("#backup_decryption_modal .notice").hide();
      this.backup_decryption_callback(backup_file);
    }
  }

  cloud_upload(backup_file, delete_after_transfer) {
    delete_after_transfer = delete_after_transfer || 0;

    jQuery("#remote_storage_modal").find(".backup_name").text(backup_file);
    jQuery("#remote_storage_modal").find("input.backup_name").val(backup_file);
    M.updateTextFields();
    jQuery(".col select").formSelect();
    remote_storage_modal.open();
    jQuery("#remote_storage_modal .status").hide();

    jQuery(".remote-storage-form")
      .off("submit")
      .on("submit", function () {
        jQuery("#remote_storage_modal .status").show();
        jQuery("#remote_storage_modal .status .progress .indeterminate")
          .removeClass("determinate")
          .css("width", "0%");
        jQuery("#remote_storage_modal .status-text")
          .removeClass("error")
          .text("");

        var storage_type = jQuery("#remote_storage_modal select").val();

        if (backup_file) {
          jQuery.ajax({
            url: XCLONER_AJAXURL,
            method: "post",
            data: {
              action: "upload_backup_to_remote",
              file: backup_file,
              storage_type: storage_type,
              delete_after_transfer: delete_after_transfer,
            },
            success: function (response) {
              if (response.error) {
                jQuery("#remote_storage_modal .status-text")
                  .addClass("error")
                  .text(response.message);
              } else {
                jQuery("#remote_storage_modal .status-text")
                  .removeClass("error")
                  .text("done");
              }

              jQuery("#remote_storage_modal .status .progress .indeterminate")
                .addClass("determinate")
                .css("width", "100%");
            },
            error: function (xhr, textStatus, error) {
              jQuery("#remote_storage_modal .status-text")
                .addClass("error")
                .text(textStatus + error);
            },
            dataType: "json",
          });
        }

        return false;
      });
  }

  copy_remote_to_local(backup_file) {
    local_storage_upload_modal.open();
    jQuery("#local_storage_upload_modal .modal-content .backup-name").text(
      backup_file
    );
    jQuery("#local_storage_upload_modal .status-text")
      .removeClass("error")
      .text("");
    jQuery("#local_storage_upload_modal .status .progress .indeterminate")
      .removeClass("determinate")
      .css("width", "0%");

    if (backup_file) {
      jQuery.ajax({
        url: XCLONER_AJAXURL,
        method: "post",
        data: {
          action: "copy_backup_remote_to_local",
          file: backup_file,
          storage_type: this.storage_selection,
        },
        success: function (response) {
          if (response.error) {
            jQuery("#local_storage_upload_modal .status-text")
              .addClass("error")
              .text(response.message);
          } else {
            jQuery("#local_storage_upload_modal .status-text")
              .removeClass("error")
              .text("done");
          }

          jQuery("#local_storage_upload_modal .status .progress .indeterminate")
            .addClass("determinate")
            .css("width", "100%");
        },
        error: function (xhr, textStatus, error) {
          jQuery("#local_storage_upload_modal .status-text")
            .addClass("error")
            .text(textStatus + error);
        },
        dataType: "json",
      });
    }
  }

  //end class
}

