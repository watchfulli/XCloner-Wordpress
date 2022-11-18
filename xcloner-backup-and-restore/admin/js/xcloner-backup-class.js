
export class Xcloner_Backup {
  constructor() {
    this.cancel = 0;
    this.params = {};
    this.last_dumpfile = "";
    this.last_backup_file = "";
    this.backup_part = 0;
    this.backup_size_total = 0;
    this.resume = {};
  }

  get_form_params() {
    var table_params = [];
    var files_params = [];
    var extra = [];

    jQuery.each(
      jQuery("#jstree_database_container").jstree("get_checked", true),
      function () {
        var object = {};
        object.id = this.id;
        object.parent = this.parent;

        var index = table_params.length;
        table_params[index] = object;
      }
    );

    jQuery.each(
      jQuery("#jstree_files_container").jstree("get_checked", true),
      function () {
        //console.log(this.id+"-"+this.parent);

        var object = {};
        object.id = this.id;
        object.parent = this.parent;

        var index = files_params.length;
        files_params[index] = object;
      }
    );

    var $return = {};
    $return.table_params = table_params;
    $return.files_params = files_params;
    $return.backup_params = jQuery("#generate_backup_form").serializeArray();

    return $return;
  }

  do_backup_database_callback(elem, action, json) {
    if (json.extra) this.params.extra = json.extra;

    if (json.extra.stats) {
      if (json.extra.stats.tables_count !== undefined) {
        jQuery(elem)
          .find(".table-counter")
          .text(parseInt(json.extra.stats.tables_count));
      }

      if (json.extra.stats.database_count !== undefined) {
        jQuery(elem)
          .find(".database-counter")
          .text(parseInt(json.extra.stats.database_count));
      }

      if (json.extra.stats.total_records !== undefined) {
        jQuery(elem)
          .find(".total-records")
          .text(parseInt(json.extra.stats.total_records));
      }
    }

    if (json.extra.tableName) {
      jQuery(elem)
        .find(".last-logged-table")
        .text(
          json.extra.databaseName +
            "." +
            json.extra.tableName +
            " (" +
            json.extra.processedRecords +
            " records)"
        );
    }

    if (
      json.extra.processedRecords !== undefined &&
      !json.extra.startAtRecord &&
      !json.extra.endDump
    ) {
      var records =
        parseInt(jQuery(elem).find(".total-records").attr("data-processed")) +
        parseInt(json.extra.processedRecords);

      var percent =
        (100 * parseInt(records)) /
        parseInt(jQuery(elem).find(".total-records").text());
      jQuery(elem)
        .find(".progress .determinate")
        .css("width", percent + "%");

      jQuery(elem).find(".total-records").attr("data-processed", records);
      jQuery(elem)
        .find(".status-body ul.logged-tables")
        .prepend(
          jQuery("<li>").text(
            json.extra.databaseName +
              "." +
              json.extra.tableName +
              " (" +
              json.extra.processedRecords +
              " records)"
          )
        );
    }

    if (json.extra.dumpfile !== undefined) {
      var db_text =
        json.extra.dumpfile +
        " (" +
        this.getSize(json.extra.dumpsize, 1024) +
        " KB)";

      if (!jQuery(this.last_dumpfile).hasClass(json.extra.dumpfile)) {
        this.last_dumpfile = jQuery("<li>")
          .addClass(json.extra.dumpfile)
          .html(db_text)
          .prependTo("ul.logged-databases");
      } else {
        jQuery(this.last_dumpfile).html(db_text);
      }
    }

    if (!json.finished /*&& !this.cancel*/) {
      this.do_ajax(elem, action);
      return false;
    }

    jQuery(elem).find(".last-logged-table").text("done");
    jQuery(elem).find(".progress .determinate").css("width", "100%");

    this.do_backup_files();
  }

  do_backup_database() {
    if (!jQuery("#jstree_database_container").length) {
      this.do_backup_files();
      return;
    }

    /*if(this.cancel)
            return false;*/

    var elem = "#generate_backup ul.backup-status li.database-backup";
    jQuery(elem).show();
    //jQuery(elem + ' .status-body').show();

    jQuery(elem).find(".collapsible-header").trigger("click");

    jQuery(elem).find(".total-records").text(0);
    jQuery(elem).find(".total-records").attr("data-processed", 0);
    jQuery(elem).find(".table-counter").text(0);
    jQuery(elem).find(".database-counter").text(0);
    jQuery(elem).find(".logged-databases").html("");
    jQuery(elem).find(".logged-tables").html("");

    this.last_dumpfile = 0;

    jQuery(elem).find(".progress .determinate").css("width", "0%");

    this.do_ajax(elem, "backup_database", 1);
  }

  do_scan_filesystem_callback(elem, action, json) {
    if (json.total_files_num) {
      jQuery(".file-system .file-counter").text(
        parseInt(json.total_files_num) +
          parseInt(jQuery(".file-system .file-counter").text())
      );
    }

    if (json.total_files_size) {
      var size =
        parseFloat(json.total_files_size) +
        parseFloat(jQuery(".file-system .file-size-total").text());
      jQuery(".file-system .file-size-total").text(size.toFixed(2));
    }

    if (json.last_logged_file) {
      jQuery(".file-system .last-logged-file").text(json.last_logged_file);
    }

    if (!json.finished /*&& !this.cancel*/) {
      this.do_ajax(elem, action);
      return false;
    }

    //finished
    jQuery(elem)
      .find(".progress .indeterminate")
      .removeClass("indeterminate")
      .addClass("determinate")
      .css("width", "100%");
    jQuery(".file-system .last-logged-file").text("done");

    //this.do_backup_database();
    this.do_backup_database();
  }

  do_scan_filesystem() {
    /*if(this.cancel)
            return false;*/

    var elem = "#generate_backup ul.backup-status li.file-system";
    jQuery(elem).show();
    jQuery(elem + " .status-body").show();
    jQuery(elem).find(".collapsible-header").trigger("click");

    jQuery(".file-system .file-counter").text(0);
    jQuery(".file-system .last-logged-file").text("");
    jQuery(".file-system .file-size-total").text(0);
    jQuery(".file-system .progress div")
      .removeClass("determinate")
      .addClass("indeterminate")
      .css("width", "0%");

    this.do_ajax(elem, "scan_filesystem", 1);
  }

  do_backup_files_callback(elem, action, json) {
    /*if(this.cancel)
            return false;*/

    if (json.extra) {
      this.params.extra = json.extra;
    }

    if (json.extra) {
      if (json.extra.start_at_line !== undefined) {
        jQuery(elem)
          .find(".file-counter")
          .text(parseInt(json.extra.start_at_line));
      }

      if (json.extra.start_at_line !== undefined) {
        //var prev_backup_size = parseInt(jQuery(elem).find(".file-size-total").attr('data-processed'));
        jQuery(elem)
          .find(".file-size-total")
          .text(
            this.getSize(
              this.backup_size_total + parseInt(json.extra.backup_size)
            )
          );
        //var backup_size = parseInt(json.extra.backup_size);
      }
    }

    if (json.extra.processed_file) {
      if (json.extra.start_at_byte !== undefined && json.extra.start_at_byte) {
        var processed_size = json.extra.start_at_byte;
      } else {
        var processed_size = json.extra.processed_file_size;
      }

      jQuery(elem)
        .find(".last-logged-file")
        .text(
          json.extra.processed_file +
            " (" +
            this.getSize(processed_size, 1024) +
            " KB)"
        );
    }

    if (json.extra.processed_file !== undefined) {
      var backup_text =
        json.extra.backup_archive_name_full +
        " (" +
        this.getSize(json.extra.backup_size) +
        ") MB";

      if (this.backup_part != json.extra.backup_part) {
        this.backup_part = json.extra.backup_part;
        this.backup_size_total =
          this.backup_size_total + json.extra.backup_size;
      }

      if (
        !jQuery(this.last_backup_file).hasClass(json.extra.backup_archive_name)
      ) {
        this.last_backup_file = jQuery("<li>")
          .addClass(json.extra.backup_archive_name)
          .html(backup_text)
          .prependTo(jQuery(elem).find(".status-body .backup-name"));
      }

      jQuery(this.last_backup_file).html(backup_text);
    }

    if (json.extra.lines_total) {
      var percent =
        (100 * parseInt(json.extra.start_at_line)) /
        parseInt(json.extra.lines_total);
      jQuery(elem)
        .find(".progress .determinate")
        .css("width", percent + "%");
    }

    if (!json.finished /*&& !this.cancel*/) {
      this.do_ajax(elem, action);
      return false;
    }

    jQuery(elem).find(".last-logged-file").text("done");
    jQuery(".backup-done .cloud-upload").attr(
      "href",
      "#" + json.extra.backup_parent
    );
    jQuery(".backup-done .download").attr(
      "href",
      "#" + json.extra.backup_parent
    );
    jQuery(".backup-done .list-backup-content").attr(
      "href",
      "#" + json.extra.backup_parent
    );

    //if (jQuery("#backup_options #backup_encrypt").is(":checked")) {
    if (this.do_backup_encryption()) {
      return;
    }

    if (this.do_remote_storage_upload(json.extra.backup_parent)) {
      return;
    }

    //this.restart_backup();
    this.do_backup_done(json.extra.backup_parent);
  }

  do_backup_files() {
    if (this.cancel) return false;

    var elem = "#generate_backup ul.backup-status li.files-backup";
    jQuery(elem).show();
    jQuery(elem + " .status-body").show();
    jQuery(elem).find(".collapsible-header").trigger("click");

    jQuery(elem).find(".file-size-total").text(0);
    jQuery(elem).find(".file-size-total").attr("data-processed", 0);
    jQuery(elem).find(".file-counter").text(0);
    jQuery(elem).find(".last-logged-file").text("");

    jQuery(elem).find(".progress .determinate").css("width", "0%");

    this.do_ajax(elem, "backup_files", 1);
  }

  do_backup_encryption_callback(elem, action, response) {
    if (response.extra) {
      this.params.extra = response.extra;
    }

    jQuery(".backup-encryption .last-logged-file").text(
      "encrypting " + response.processing_file + " ..."
    );

    if (response.total_size !== undefined) {
      jQuery(".backup-encryption .progress > div")
        .removeClass("indeterminate")
        .addClass("determinate");
      var percent =
        parseInt(response.start * 100) / parseInt(response.total_size);
      jQuery(".backup-encryption .progress .determinate").css(
        "width",
        parseInt(percent) + "%"
      );
    }

    if (response.error) {
      jQuery(".backup-encryption .status-body").show();
      jQuery(".backup-encryption .status-body")
        .addClass("error")
        .prepend(response.message + " ");
      jQuery(".backup-encryption .progress > div")
        .addClass("determinate")
        .removeClass("indeterminate")
        .css("width", "100%");
      return;
    }

    if (!response.finished /*&& !this.cancel*/) {
      this.do_ajax(elem, action);
      return false;
    }

    //finished
    jQuery(elem).find(".progress > div").css("width", "100%");
    jQuery(".backup-encryption .last-logged-file").text("done");

    //this.do_backup_database();
    if (this.do_remote_storage_upload(response.processing_file)) {
      return;
    }

    this.do_backup_done(response.processing_file);
  }

  do_backup_encryption() {
    if (this.cancel) {
      return false;
    }

    if (!jQuery("#backup_options #backup_encrypt").is(":checked")) {
      return false;
    }

    var elem = "#generate_backup ul.backup-status li.backup-encryption";
    jQuery(elem).show();
    jQuery(elem + " .status-body").show();
    jQuery(elem).find(".collapsible-header").trigger("click");

    jQuery(elem).find(".progress .determinate").removeClass('determinate').addClass('indeterminate');

    this.do_ajax(elem, "backup_encryption", 1);

    return true;
  }

  do_upload_backup_to_remote_callback(elem, action, response) {

    if(!response || response.error) {
        jQuery(elem).find('.status').text(response.message);
        jQuery(elem).find(".progress .indeterminate").removeClass('indeterminate').addClass('determinate').css("width", "0%");
    }else{
        jQuery(elem).find('.status').text('done');
        jQuery(elem).find(".progress .indeterminate").removeClass('indeterminate').addClass('determinate').css("width", "100%");
    }

    this.do_backup_done();
  }

  do_remote_storage_upload(backup_name) {
    if (this.cancel) {
      return false;
    }

    var remote_storage = jQuery("#schedule_storage").val();

    if (!remote_storage) {
      return false;
    }

    var delete_backup_after_transfer = Number(
      jQuery("#backup_delete_after_remote_transfer").is(":checked")
    );

    jQuery("#remote_storage_modal #transfer_storage").val(remote_storage);

    var elem = "#generate_backup ul.backup-status li.remote-storage-transfer";
    jQuery(elem).show();
    jQuery(elem + " .status-body").show();
    jQuery(elem).find(".collapsible-header").trigger("click");

    jQuery(elem).find(".progress .determinate").removeClass('determinate').addClass('indeterminate');

    this.params.file =backup_name;
    this.params.storage_type= remote_storage;
    this.params.delete_after_transfer= delete_backup_after_transfer;

    this.do_ajax(elem, "upload_backup_to_remote", 1);

    return true;
  }

  do_backup_done(backup_name) {
    var elem = "#generate_backup ul.backup-status li.backup-done";
    jQuery(elem).show();
    jQuery(elem + " .status-body").show();
    jQuery(elem).find(".collapsible-header").trigger("click");

    this.set_cancel(false);
    jQuery("#generate_backup .action-buttons a").hide();
    jQuery("#generate_backup .action-buttons .start").css(
      "display",
      "inline-block"
    );
  }

  do_save_schedule_callback(elem, action, json) {
    jQuery("#schedule_backup_success").show();
  }

  cancel_backup() {
    this.set_cancel(true);
    jQuery("#generate_backup .action-buttons a").hide();
    jQuery("#generate_backup .action-buttons .start").css(
      "display",
      "inline-block"
    );
    jQuery("#generate_backup .action-buttons .restart").css(
      "display",
      "inline-block"
    );

    //this.restart_backup();
  }

  restart_backup() {
    this.set_cancel(false);

    jQuery("#generate_backup .action-buttons a").hide();
    jQuery("#generate_backup .action-buttons .cancel").css(
      "display",
      "inline-block"
    );

    if (this.resume.action) {
      //console.log(this.resume.action)
      this.do_ajax(this.resume.elem, this.resume.action, this.resume.params);
      this.resume = {};
      return;
    }

    this.start_backup();
  }

  start_backup() {
    this.resume = {};
    this.set_cancel(false);
    jQuery("#generate_backup .action-buttons a").hide();
    jQuery("#generate_backup .action-buttons .cancel").css(
      "display",
      "inline-block"
    );

    this.backup_size_total = 0;
    this.last_backup_file = "";
    this.backup_part = 0;
    jQuery("#generate_backup ul.backup-name li").remove();

    jQuery("#generate_backup ul.backup-status > li").hide();
    jQuery("#generate_backup .backup-status").show();

    this.params = this.get_form_params();

    this.do_scan_filesystem();
  }

  set_cancel(status) {
    if (status) {
      //document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0, class: 'determinate' }}));
      //jQuery("#generate_backup .collapsible-header.active .progress > div").add
    }
    this.cancel = status;
  }

  get_cancel(status) {
    return this.cancel;
  }

  init_resume(elem, action, init) {
    this.resume.elem = elem;
    this.resume.action = action;
    this.resume.init = init;
  }

  do_ajax(elem, action, init = 0) {
    let hash = !this.params.hash ? 'generate_hash' : this.params.hash;

    if (this.cancel == true) {
      this.init_resume(elem, action, init);
      return;
    }

    var callback = "do_" + action + "_callback";
    var data = JSON.stringify(this.params);
    var $this = this;

    jQuery
      .ajax({
        url: XCLONER_AJAXURL,
        dataType: "json",
        type: "POST",
        data: {
          action: action,
          data: data,
          init: init,
          hash: hash,
          API_ID: ID(),
        },
        error: function (err) {
          show_ajax_error("Communication Error", "", err);
          $this.init_resume(elem, action, init);
        },
      })
      .done(function (json) {
        if (json.hash) {
          $this.params.hash = json.hash;
        }
        if (json.error !== undefined) {
          show_ajax_error("Communication Error", "", json.error_message);
          $this.init_resume(elem, action, init);
          return;
        }

        $this.resume = {};

        $this[callback](elem, action, json);
      });
  }

  getSize(bytes, conv = 1024 * 1024) {
    return (bytes / conv).toFixed(2);
  }
}
