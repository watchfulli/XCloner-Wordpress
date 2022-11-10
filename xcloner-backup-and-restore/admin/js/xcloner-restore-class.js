/** global: CustomEvent */
/** global: Event */

import {ID} from "./xcloner-admin";

class Xcloner_Restore {
  constructor(local_restore = false) {
    this.steps = [
      "restore-script-upload-step",
      "backup-upload-step",
      "restore-remote-backup-step",
      "restore-remote-database-step",
      "restore-finish-step",
    ];
    this.ajaxurl = location.origin + ajaxurl + "?action=restore_backup";
    this.restore_script_url = "";
    this.cancel = false;
    this.resume = {};
    this.hash = null;
    this.file_counter = 0;
    this.local_restore = local_restore;
    this.current_step = 0;

    this.set_options_by_restore_location();

    document.addEventListener(
        "backup_upload_finish",
        function (e) {
          jQuery(".xcloner-restore .backup-upload-step .toggler").removeClass(
              "cancel"
          );
        },
        false
    );

    document.addEventListener(
        "remote_restore_backup_finish",
        function (e) {
          jQuery(
              ".xcloner-restore .restore-remote-backup-step .toggler"
          ).removeClass("cancel");
        },
        false
    );

    document.addEventListener(
        "remote_restore_mysql_backup_finish",
        function (e) {
          jQuery(
              ".xcloner-restore .restore-remote-database-step .toggler"
          ).removeClass("cancel");
        },
        false
    );

    document.addEventListener(
        "restore_script_invalid",
        function (e) {
          jQuery(".xcloner-restore #restore_script_url")
              .addClass("invalid")
              .removeClass("valid");
          jQuery(".xcloner-restore #validate_url .material-icons")
              .text("error");
        },
        false
    );

    document.addEventListener(
        "restore_script_valid",
        function (e) {
          jQuery(".xcloner-restore #validate_url .material-icons").text(
              "check_circle"
          );
          jQuery(".xcloner-restore #restore_script_url")
              .removeClass("invalid")
              .addClass("valid");
        },
        false
    );

    document.addEventListener(
        "xcloner_populate_remote_backup_files_list",
        function (e) {
          var files = e.detail.files;
          var original_value = jQuery(
              ".xcloner-restore #remote_backup_file"
          ).val();

          jQuery(".xcloner-restore #remote_backup_file")
              .find("option")
              .not(":first")
              .remove();

          for (var key in files) {
            var selected = "not-selected";

            if (files[key].selected || original_value == files[key].path) {
              selected = "selected";
            }

            jQuery(".xcloner-restore #remote_backup_file")
                .append(
                    "<option value='" +
                    files[key].path +
                    "' " +
                    selected +
                    ">" +
                    files[key].path +
                    "(" +
                    e.detail.$this.getSize(files[key].file_size) +
                    " MB)" +
                    "</option>"
                )
                .addClass("file");
          }
        },
        false
    );

    document.addEventListener(
        "xcloner_populate_remote_mysqldump_files_list",
        function (e) {
          var files = e.detail.files;
          var original_value = jQuery(
              ".xcloner-restore #remote_database_file"
          ).val();

          jQuery(".xcloner-restore #remote_database_file")
              .find("option")
              .not(":first")
              .remove();

          for (var key in files) {
            if (files[key].selected || original_value == files[key].path)
              var selected = "selected";
            else var selected = "not-selected";

            var option = jQuery(".xcloner-restore #remote_database_file")
                .append(
                    "<option value='" +
                    files[key].path +
                    "' " +
                    selected +
                    ">" +
                    files[key].path +
                    "(" +
                    e.detail.$this.getSize(files[key].size) +
                    " MB) " +
                    files[key].timestamp +
                    "</option>"
                )
                .addClass("file");
          }
        },
        false
    );

    document.addEventListener(
        "xcloner_restore_update_progress",
        function (e) {
          if (e.detail.percent !== undefined) {
            jQuery(".xcloner-restore .steps.active .progress").show();

            if (e.detail.class == "indeterminate") {
              jQuery(".xcloner-restore .steps.active .progress > div")
                  .addClass(e.detail.class)
                  .removeClass("determinate");
            }

            if (e.detail.class == "determinate") {
              jQuery(".xcloner-restore .steps.active .progress > div")
                  .addClass(e.detail.class)
                  .removeClass("indeterminate");
            }

            if (e.detail.percent == 100) {
              jQuery(".xcloner-restore .steps.active .progress > div")
                  .removeClass("indeterminate")
                  .addClass("determinate")
                  .css("width", e.detail.percent + "%");
            } else {
              jQuery(".xcloner-restore .steps.active .progress .determinate")
                  .css(
                      "width",
                      e.detail.percent + "%"
                  );
            }
          }
        },
        false
    );

    document.addEventListener(
        "xcloner_restore_display_status_text",
        function (e) {
          if (e.detail.status === undefined) e.detail.status = "updated";

          if (e.detail.message !== undefined) {
            jQuery(".xcloner-restore .steps.active .status").html(
                "<div class='" +
                e.detail.status +
                "'>" +
                e.detail.message +
                "</div>"
            );
          }
        },
        false
    );

    document.addEventListener(
        "xcloner_populate_remote_restore_path",
        function (e) {
          //dir: response.statusText.dir, restore_script_url: response.statusText.restore_script_url
          if (e.detail.dir !== undefined) {
            if (!jQuery(".xcloner-restore #remote_restore_path").val()) {
              jQuery(".xcloner-restore #remote_restore_path").val(e.detail.dir);
            }
          }

          if (e.detail.restore_script_url !== undefined) {
            if (!jQuery(".xcloner-restore #remote_restore_url").val()) {
              jQuery(".xcloner-restore #remote_restore_url").val(
                  e.detail.restore_script_url
              );
            }

            if (!jQuery(".xcloner-restore #remote_restore_site_url").val()) {
              jQuery(".xcloner-restore #remote_restore_site_url").val(
                  e.detail.restore_script_url
              );
            }

            if (!jQuery(".xcloner-restore #remote_mysql_host").val()) {
              jQuery(".xcloner-restore #remote_mysql_host").val(
                  e.detail.remote_mysql_host
              );
            }
            if (!jQuery(".xcloner-restore #remote_mysql_db").val()) {
              jQuery(".xcloner-restore #remote_mysql_db").val(
                  e.detail.remote_mysql_db
              );
            }
            if (!jQuery(".xcloner-restore #remote_mysql_user").val()) {
              jQuery(".xcloner-restore #remote_mysql_user").val(
                  e.detail.remote_mysql_user
              );
            }

            if (!jQuery(".xcloner-restore #remote_mysql_pass").val()) {
              jQuery(".xcloner-restore #remote_mysql_pass").val(
                  e.detail.remote_mysql_pass
              );
            }
          }
        },
        false
    );

    document.addEventListener(
        "remote_restore_update_files_list",
        function (e) {
          const listElement = jQuery(
              ".xcloner-restore .restore-remote-backup-step .files-list");

          if (e.detail.files === undefined || !e.detail.files.length) {
            listElement.html('');
            return;
          }

          const filesText = e.detail.files.map((file) => {
            return `<li>${file}</li>`;
          })
              .reverse()
              .slice(0, 20)
              .join('\n');

          listElement.html(filesText);

          if (e.detail.files.length > 20) {
            listElement.append(
                `<li>...${e.detail.files.length - 20} more</li>`);
          }
        },
        false
    );

    document.addEventListener(
        "xcloner_restore_display_query_box",
        function (e) {
          if (e.detail.query) {
            jQuery(".xcloner-restore .query-box").show();
            jQuery(".xcloner-restore .query-list").val(e.detail.query);
          } else {
            jQuery(".xcloner-restore .query-box").hide();
            jQuery(".xcloner-restore .query-list").val("");
          }
        },
        false
    );

    document.addEventListener(
        "xcloner_restore_finish",
        function (e) {
          jQuery(".xcloner-restore #xcloner_restore_finish").show();
          jQuery("#restore_finish").hide();
        },
        false
    );
  }

  set_options_by_restore_location() {
    const remoteRestoreElement = jQuery("#remote-restore-options");
    const updateRemoteSiteUrlElement = jQuery("#update_remote_site_url");
    const deleteBackupArchiveElement = jQuery("#delete_backup_archive");
    const deleteRestoreScriptElement = jQuery("#delete_restore_script");
    const filterInputElement = jQuery(
        ".xcloner-restore input[name=filter_files]");
    const filterFilesAllElement = jQuery("#filter_files_all");
    const filterFilesWpContentElement = jQuery("#filter_files_wp_content");

    filterInputElement.val(
        [
          this.local_restore ?
              filterFilesWpContentElement.val() :
              filterFilesAllElement.val()]
    );

    if (this.local_restore) {
      this.restore_script_url = this.ajaxurl;
      remoteRestoreElement.hide();
      deleteBackupArchiveElement
          .attr("disabled", "disabled")
          .removeAttr("checked");
      deleteRestoreScriptElement
          .attr("disabled", "disabled")
          .removeAttr("checked");
      filterFilesAllElement
          .removeAttr("checked")
          .attr("disabled", "disabled")
          .parent()
          .attr("data-tooltip", "Available only when cloning a site")
          .tooltip();
    } else {
      remoteRestoreElement.show();
      updateRemoteSiteUrlElement
          .attr("checked", "checked").removeAttr("disabled");
      deleteRestoreScriptElement
          .attr("checked", "checked")
          .removeAttr("disabled");
      filterFilesAllElement
          .removeAttr("disabled");
    }
  }

  get_remote_backup_files_callback(response, status) {
    this.get_remote_restore_path_default();
    if (!status) {
      return
    }
    var files = response.statusText.files;
    document.dispatchEvent(
        new CustomEvent("xcloner_populate_remote_backup_files_list", {
          detail: {files: files, $this: this},
        })
    );
  }

  get_remote_backup_files() {
    this.set_cancel(false);

    var params = {};
    params.local_backup_file = jQuery(
        ".xcloner-restore .backup-upload-step #backup_file"
    ).val();

    this.do_ajax(
        "get_remote_backup_files_callback",
        "list_backup_archives",
        params,
        true
    );
  }

  get_remote_mysqldump_files_callback(response, status) {
    if (status) {
      var files = response.statusText.files;
      document.dispatchEvent(
          new CustomEvent("xcloner_populate_remote_mysqldump_files_list", {
            detail: {files: files, $this: this},
          })
      );
    }
  }

  get_remote_mysqldump_files() {
    this.set_cancel(false);
    this.resume.callback = "";

    if (this.resume.callback == "get_remote_mysqldump_files_callback") {
      this.do_ajax(
          this.resume.callback,
          this.resume.action,
          this.resume.params
      );
      this.resume = {};
      return;
    }

    var params = {};
    params.backup_file = this.get_backup_file();
    params.remote_path = this.get_remote_path();

    this.do_ajax(
        "get_remote_mysqldump_files_callback",
        "list_mysqldump_backups",
        params,
        true
    );
  }

  get_backup_file() {
    return jQuery(".xcloner-restore #remote_backup_file").val();
  }

  get_remote_path() {
    return jQuery(".xcloner-restore #remote_restore_path").val();
  }

  get_remote_restore_path_default_callback(
      response,
      status,
      params = {}
  ) {
    if (status) {
      document.dispatchEvent(
          new CustomEvent("xcloner_populate_remote_restore_path", {
            detail: response.statusText,
          })
      );
    }
  }

  get_remote_restore_path_default() {
    this.set_cancel(false);

    var params = {};

    params.restore_script_url = this.restore_script_url;

    this.do_ajax(
        "get_remote_restore_path_default_callback",
        "get_current_directory",
        params,
        true
    );
  }

  remote_restore_backup_file_callback(response, status, params = {}) {
    if (!status) {
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              status: "error",
              message: response.status + " " + response.statusText,
            },
          })
      );
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_update_progress", {
            detail: {percent: 100},
          })
      );
      document.dispatchEvent(new CustomEvent("remote_restore_backup_finish"));
      return;
    }

    var processed =
        parseInt(response.statusText.start) +
        parseInt(response.statusText.processed);

    if (response.statusText.extracted_files &&
        response.statusText.extracted_files.length) {
      document.dispatchEvent(
          new CustomEvent("remote_restore_update_files_list", {
            detail: {files: response.statusText.extracted_files},
          })
      );
    }

    if (!response.statusText.finished) {
      params.start = response.statusText.start;
      params.part = response.statusText.part;
      params.processed = response.statusText.processed;

      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              message:
                  "Processing <strong>" +
                  response.statusText.backup_file +
                  "</strong>- processed " +
                  this.getSize(processed, 1024) +
                  " KB from archive",
            },
          })
      );

      this.do_ajax(
          "remote_restore_backup_file_callback",
          "restore_backup_to_path",
          params,
          true
      );
      return;
    }

    document.dispatchEvent(
        new CustomEvent("xcloner_restore_update_progress", {
          detail: {percent: 100},
        })
    );
    document.dispatchEvent(
        new CustomEvent("xcloner_restore_display_status_text", {
          detail: {
            message:
                "Done restoring <strong>" +
                response.statusText.backup_file +
                "</strong>.",
          },
        })
    );
    document.dispatchEvent(new CustomEvent("remote_restore_backup_finish"));
    this.cancel = false;
  }

  remote_restore_backup_file() {
    var params = {};
    params.backup_file = this.get_backup_file();
    params.remote_path = this.get_remote_path();
    params.filter_files = jQuery(
        ".xcloner-restore input[name=filter_files]:checked").val();
    if (this.resume.callback == "remote_restore_backup_file_callback") {
      this.do_ajax(
          this.resume.callback,
          this.resume.action,
          this.resume.params
      );
      this.resume = {};
      return;
    } else {
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_update_progress", {
            detail: {
              percent: 0,
              class: "indeterminate",
            },
          })
      );
    }
    document.dispatchEvent(
        new CustomEvent("remote_restore_update_files_list", {
          detail: {files: ""},
        })
    );
    this.do_ajax(
        "remote_restore_backup_file_callback",
        "restore_backup_to_path",
        params,
        true
    );
  }

  remote_restore_mysql_backup_file_callback(
      response,
      status,
      params = {}
  ) {
    if (!response) {
      this.set_cancel(true);

      document.dispatchEvent(
          new CustomEvent("remote_restore_mysql_backup_finish")
      );
      return;
    }
    if (!status) {
      this.start = response.statusText.start;

      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_query_box", {
            detail: {query: response.statusText.query},
          })
      );

      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              status: "error",
              message: response.status + " " + response.statusText.message,
            },
          })
      );
      //document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
      document.dispatchEvent(
          new CustomEvent("remote_restore_mysql_backup_finish")
      );
      //this.set_cancel(true);
      return;
    }

    document.dispatchEvent(
        new CustomEvent("xcloner_restore_display_query_box", {
          detail: {query: ""},
        })
    );
    params.query = "";

    var processed =
        parseInt(response.statusText.start) +
        parseInt(response.statusText.processed);

    if (!response.statusText.finished) {
      params.start = response.statusText.start;
      params.processed = response.statusText.processed;
      this.start = params.start;

      var percent = 0;

      if (response.statusText.backup_size)
        percent =
            (100 * parseInt(response.statusText.start)) /
            parseInt(response.statusText.backup_size);

      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              message:
                  "Processing <strong>" +
                  response.statusText.backup_file +
                  "</strong>- wrote " +
                  this.getSize(response.statusText.start, 1024) +
                  " KB of data",
            },
          })
      );
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_update_progress", {
            detail: {percent: percent},
          })
      );

      this.do_ajax(
          "remote_restore_mysql_backup_file_callback",
          "restore_mysql_backup",
          params,
          true
      );
      return;
    }

    document.dispatchEvent(
        new CustomEvent("xcloner_restore_update_progress", {
          detail: {percent: 100},
        })
    );
    document.dispatchEvent(
        new CustomEvent("xcloner_restore_display_status_text", {
          detail: {
            message:
                "Done restoring <strong>" +
                response.statusText.backup_file +
                "</strong>.",
          },
        })
    );
    document.dispatchEvent(
        new CustomEvent("remote_restore_mysql_backup_finish")
    );
    this.cancel = false;
  }

  remote_restore_mysql_backup_file(mysqldump_file) {
    this.set_cancel(false);

    var params = {};

    params.remote_mysql_host = jQuery(
        ".xcloner-restore #remote_mysql_host"
    ).val();
    params.remote_mysql_db = jQuery(".xcloner-restore #remote_mysql_db").val();
    params.remote_mysql_user = jQuery(
        ".xcloner-restore #remote_mysql_user"
    ).val();
    params.remote_mysql_pass = jQuery(
        ".xcloner-restore #remote_mysql_pass"
    ).val();
    params.remote_path = jQuery(".xcloner-restore #remote_restore_path").val();

    params.wp_home_url = jQuery(".xcloner-restore #wp_home_url").val();
    params.remote_restore_url = jQuery(
        ".xcloner-restore #remote_restore_url"
    ).val();

    if (jQuery(".xcloner-restore #wp_site_url").length) {
      params.wp_site_url = jQuery(".xcloner-restore #wp_site_url").val();
      params.restore_site_url = jQuery(
          ".xcloner-restore #remote_restore_site_url"
      ).val();
    }

    params.mysqldump_file = mysqldump_file;
    params.query = "";
    params.start = 0;

    if (jQuery(".xcloner-restore .query-box .query-list").val()) {
      params.query = jQuery(".xcloner-restore .query-box .query-list").val();
      params.start = this.start;
    }

    document.dispatchEvent(
        new CustomEvent("xcloner_restore_display_query_box", {
          detail: {query: ""},
        })
    );

    if (this.resume.callback == "remote_restore_mysql_backup_file_callback") {
      this.do_ajax(
          this.resume.callback,
          this.resume.action,
          this.resume.params
      );
      this.resume = {};
      return;
    }

    this.do_ajax(
        "remote_restore_mysql_backup_file_callback",
        "restore_mysql_backup",
        params,
        true
    );
  }

  restore_finish_callback(response, status, params = {}) {
    if (status) {
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {message: response.statusText, $this: this},
          })
      );
    } else {
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              status: "error",
              message: response.statusText,
              $this: this,
            },
          })
      );
      return false;
    }

    document.dispatchEvent(
        new CustomEvent("xcloner_restore_finish", {
          detail: {message: response.statusText, $this: this},
        })
    );
  }

  restore_finish() {
    this.set_cancel(false);

    var params = {};

    params.backup_archive = jQuery(
        ".xcloner-restore #remote_backup_file"
    ).val();
    params.remote_mysql_host = jQuery(
        ".xcloner-restore #remote_mysql_host"
    ).val();
    params.remote_mysql_db = jQuery(".xcloner-restore #remote_mysql_db").val();
    params.remote_mysql_user = jQuery(
        ".xcloner-restore #remote_mysql_user"
    ).val();
    params.remote_mysql_pass = jQuery(
        ".xcloner-restore #remote_mysql_pass"
    ).val();
    params.remote_path = jQuery(".xcloner-restore #remote_restore_path").val();
    params.remote_restore_url = jQuery(
        ".xcloner-restore #remote_restore_url"
    ).val();

    params.delete_backup_temporary_folder = 0;
    params.delete_restore_script = 0;
    params.update_remote_site_url = 0;

    if (
        jQuery(".xcloner-restore #delete_backup_temporary_folder")
            .is(":checked")
    )
      params.delete_backup_temporary_folder = 1;

    if (jQuery(".xcloner-restore #delete_restore_script").is(":checked"))
      params.delete_restore_script = 1;

    if (jQuery(".xcloner-restore #update_remote_site_url").is(":checked"))
      params.update_remote_site_url = 1;

    if (jQuery(".xcloner-restore #delete_backup_archive").is(":checked"))
      params.delete_backup_archive = 1;

    this.do_ajax(
        "restore_finish_callback",
        "restore_finish",
        params,
        true
    );
  }

  upload_backup_file(file) {
    var params = {};
    this.set_cancel(false);

    if (this.resume.callback == "upload_backup_file_callback") {
      this.do_ajax(
          this.resume.callback,
          this.resume.action,
          this.resume.params
      );
      this.resume = {};
      return;
    }

    params.file = file;
    params.start = 0;
    params.target_url = this.restore_script_url;
    params.action = "restore_upload_backup";

    document.dispatchEvent(new CustomEvent("backup_upload_start"));
    document.dispatchEvent(
        new CustomEvent("xcloner_restore_display_status_text", {
          detail: {message: "Uploading backup 0%"},
        })
    );
    document.dispatchEvent(
        new CustomEvent("xcloner_restore_update_progress", {
          detail: {percent: 0},
        })
    );

    this.do_ajax(
        "upload_backup_file_callback",
        "restore_upload_backup",
        params,
        false
    );
  }

  upload_backup_file_callback(response, status, params = {}) {
    if (!status) {
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              status: "error",
              message: response.status + " " + response.statusText,
            },
          })
      );
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_update_progress", {
            detail: {percent: 100},
          })
      );
      return;
    }

    if (
        response &&
        (response.start !== false || response.part < response.total_parts)
    ) {
      var percent = 0;
      if (response.total_size) {
        if (!response.start) response.start = 0;
        var size = parseInt(response.start) + parseInt(response.uploaded_size);
        percent = (100 * parseInt(size)) / parseInt(response.total_size);
      }

      var part_text = "";
      if (response.part > 0) part_text = "part " + response.part + " -  ";

      document.dispatchEvent(
          new CustomEvent("xcloner_restore_update_progress", {
            detail: {percent: percent},
          })
      );
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              message:
                  "Uploading backup " +
                  part_text +
                  parseFloat(percent).toFixed(2) +
                  "%",
            },
          })
      );

      params.start = response.start;
      params.part = response.part;
      params.uploaded_size = response.uploaded_size;
      params.action = "restore_upload_backup";
      this.do_ajax(
          "upload_backup_file_callback",
          "restore_upload_backup",
          params,
          false
      );
    } else {
      this.cancel = false;
      document.dispatchEvent(new CustomEvent("backup_upload_finish"));
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_update_progress", {
            detail: {percent: 100},
          })
      );
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {message: "Done."},
          })
      );
    }
  }

  verify_restore_url_callback(response, status) {
    if (!status) {
      var href_url =
          "<a href='" +
          this.restore_script_url +
          "' target='_blank'>restore script address</a>";
      document.dispatchEvent(new CustomEvent("restore_script_invalid"));
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              status: "error",
              message:
                  "Could not access the restore script: " +
                  response.status +
                  " " +
                  response.statusText +
                  ". Please check the javascript console for more details. Are you able to see a valid JSON response of the " +
                  href_url +
                  " in your browser?",
            },
          })
      );
      //document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
    } else {
      document.dispatchEvent(new CustomEvent("restore_script_valid"));
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {message: "Validation ok."},
          })
      );

      this.next_step();
    }
  }

  verify_restore_url(response, status, params = {}) {
    jQuery("#delete_restore_script")
        .attr("checked", "checked")
        .removeAttr("disabled");

    jQuery(".restore-remote-backup-step #filter_files_all")
        .removeAttr("disabled")
        .attr("checked", "checked");
    jQuery(".restore-remote-backup-step #filter_files_wp_content").removeAttr(
        "checked"
    );

    try {
      new URL(this.restore_script_url);
    } catch (e) {
      document.dispatchEvent(new CustomEvent("restore_script_invalid"));
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              status: "error",
              message:
                  "The restore script url is not valid, please check the restore script url and try again.",
            },
          })
      );
      return;
    }

    jQuery(".xcloner-restore #xcloner_restore_finish").hide();

    this.cancel = false;
    this.current_step = 0;

    this.do_ajax("verify_restore_url_callback", "", params, true);
  }

  open_target_site(elem) {
    var url = jQuery(".xcloner-restore #remote_restore_url").val();

    if (!url) {
      jQuery(".xcloner-restore #wp_home_url").val();
    }

    jQuery(elem).attr("href", url);
  }

  next_step(inc = 1) {
    this.current_step = parseInt(this.current_step) + parseInt(inc);
    jQuery(".xcloner-restore li." + this.steps[this.current_step])
        .show()
        .find(".collapsible-header")
        .trigger("click");
  }

  list_backup_content_callback(response, status, params = {}) {
    if (response.error) {
      jQuery("#backup_cotent_modal .files-list")
          .addClass("error")
          .prepend(response.statusText);
      jQuery("#backup_cotent_modal .progress > div")
          .addClass("determinate")
          .removeClass(".indeterminate")
          .css("width", "100%");
      return;
    }

    var files_text = [];

    for (var i in response.files) {
      this.file_counter++;

      files_text[i] =
          "<li>" +
          (this.file_counter +
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
    if (!response.finished && jQuery("#backup_cotent_modal").is(":visible")) {
      params.start = response.start;
      params.part = response.part;
      this.do_ajax("list_backup_content_callback", "list_backup_files", params);
    } else {
      jQuery("#backup_cotent_modal .progress > div")
          .addClass("determinate")
          .removeClass(".indeterminate")
          .css("width", "100%");
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

    var Modalelem = document.querySelector("#backup_cotent_modal");
    let backup_cotent_modal = M.Modal.init(Modalelem);
    backup_cotent_modal.open();

    jQuery("#backup_cotent_modal .progress > div")
        .removeClass("determinate")
        .addClass("indeterminate");

    //this.list_backup_content_callback(backup_file)
    var params = {};
    params.file = backup_file;
    params.start = 0;
    params.part = 0;

    this.do_ajax("list_backup_content_callback", "list_backup_files", params);
  }

  init_resume() {
    this.resume = {};
    if (jQuery(".xcloner-restore .steps.active .progress").is(":visible"))
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_update_progress", {
            detail: {percent: 0},
          })
      );
    if (jQuery(".xcloner-restore .steps.active .status").html())
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {message: ""},
          })
      );
    document.dispatchEvent(
        new CustomEvent("remote_restore_update_files_list", {
          detail: {files: ""},
        })
    );
  }

  do_ajax(
      callback,
      action = "",
      params = {},
      remote = false,
  ) {
    params.xcloner_action = action;
    params.hash = this.hash;
    params.API_ID = ID();
    if (typeof XCLONER_WPNONCE !== undefined) {
      params._wpnonce = XCLONER_WPNONCE;
    }

    if (this.cancel === true) {
      this.resume.callback = callback;
      this.resume.action = action;
      this.resume.params = params;

      return;
    }

    var $this = this;

    jQuery(".xcloner-restore .steps.active").addClass("active_status");

    var url = remote ? this.restore_script_url : this.ajaxurl;

    var parsedUrl = new URL(url);

    this.request = jQuery
        .ajax({
          url,
          dataType: "json",
          type: "POST",
          crossDomain: true,
          data: params,
          username: parsedUrl.username,
          password: parsedUrl.password,
          error: function (xhr, status, error) {
            $this.resume.callback = callback;
            $this.resume.action = action;
            $this.resume.params = params;

            document.dispatchEvent(
                new CustomEvent("xcloner_restore_display_status_text", {
                  detail: {
                    status: "error",
                    message: xhr.status + " " + xhr.statusText,
                  },
                })
            );
            $this[callback](xhr, false);
          },
        })
        .done(function (json) {
          if (!json) {
            $this.resume.callback = callback;
            $this.resume.action = action;
            $this.resume.params = params;

            document.dispatchEvent(
                new CustomEvent("xcloner_restore_display_status_text", {
                  detail: {
                    status: "error",
                    message:
                        "Lost connection with the API, please try and authentificate again",
                  },
                })
            );
          }

          if (json && json.status != 200) {
            if (json.error) {
              document.dispatchEvent(
                  new CustomEvent("xcloner_restore_display_status_text", {
                    detail: {status: "error", message: json.message},
                  })
              );
            } else {
              $this[callback](json, false, params);
            }

            return;
          }
          $this[callback](json, true, params);
        });
  }

  set_restore_script_url(url) {
    this.restore_script_url = url;
  }

  set_cancel(status) {
    if (status) {
      //document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {append : true, message: "Cancelled" }}));
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_update_progress", {
            detail: {percent: 0, class: "determinate"},
          })
      );
    }

    this.cancel = status;
  }

  get_cancel() {
    return this.cancel;
  }

  getSize(bytes, conv = 1024 * 1024) {
    return (bytes / conv).toFixed(2);
  }
}

jQuery(document).ready(function () {
  const localRestore = typeof xcloner_local_restore === "undefined" ? false : xcloner_local_restore;
  const xclonerRestore = new Xcloner_Restore(localRestore);

  jQuery(".col select").formSelect();

  jQuery(".xcloner-restore .upload-backup.cancel").on("click", function () {
    xclonerRestore.set_cancel(true);
  });

  jQuery(".xcloner-restore .upload-backup").on("click", function () {
    if (jQuery(this).hasClass("cancel")) xclonerRestore.set_cancel(true);
    else xclonerRestore.set_cancel(false);

    var backup_file = jQuery(".xcloner-restore #backup_file").val();

    if (backup_file) {
      jQuery(this).parent().toggleClass("cancel");

      if (!xclonerRestore.get_cancel())
        xclonerRestore.upload_backup_file(backup_file);
    } else {
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              status: "error",
              message: "Please select a backup file from the list above",
            },
          })
      );
    }
  });

  jQuery("#restore_script_url_form").on("submit", function (e) {
    e.preventDefault();
    xclonerRestore.set_restore_script_url(
        jQuery(".xcloner-restore #restore_script_url").val()
    );
    xclonerRestore.verify_restore_url();
  });

  jQuery(".xcloner-restore #skip_upload_backup").on("click", function () {
    xclonerRestore.set_cancel(true);
    xclonerRestore.next_step(1);
  });

  jQuery(".xcloner-restore #skip_restore_remote_database_step").on(
      "click",
      function () {
        xclonerRestore.set_cancel(true);
        xclonerRestore.next_step();
      }
  );

  jQuery(".xcloner-restore li.steps .collapsible-header").on("click", function (e) {
    xclonerRestore.current_step = jQuery(this).parent().attr("data-step") - 1;
  });

  jQuery(".xcloner-restore #skip_remote_backup_step").on("click", function () {
    xclonerRestore.set_cancel(true);
    xclonerRestore.next_step();
  });

  jQuery(
      ".xcloner-restore .restore-remote-backup-step .collapsible-header, .xcloner-restore #refresh_remote_backup_file"
  ).click(function () {
    xclonerRestore.get_remote_backup_files();
  });

  jQuery(
      ".xcloner-restore .restore-remote-database-step .collapsible-header"
  ).click(function () {
    xclonerRestore.get_remote_mysqldump_files();
  });

  jQuery(".xcloner-restore #remote_backup_file").on("change", function () {
    xclonerRestore.init_resume();
  });

  jQuery(".xcloner-restore #backup_file").on("change", function () {
    xclonerRestore.init_resume();
  });

  jQuery(".xcloner-restore #restore_finish").click(function () {
    xclonerRestore.restore_finish();
  });

  jQuery(".xcloner-restore #open_target_site a").click(function () {
    xclonerRestore.open_target_site(this);
  });

  jQuery(".xcloner-restore #refresh_database_file").on("click", function (e) {
    document.dispatchEvent(
        new CustomEvent("xcloner_restore_update_progress", {
          detail: {percent: 0},
        })
    );
    xclonerRestore.resume = {};
    xclonerRestore.get_remote_mysqldump_files();
    e.stopPropagation();
  });

  jQuery(".xcloner-restore #toggle_file_restore_display").on("click", function (
      e
  ) {
    jQuery(".xcloner-restore .restore-remote-backup-step .files-list").toggle();
  });

  jQuery(".xcloner-restore .restore_remote_mysqldump")
      .on("click", function () {
        xclonerRestore.set_cancel(jQuery(this).hasClass("cancel"));

        this.remote_database_file = jQuery(
            ".xcloner-restore #remote_database_file"
        ).val();

        if (!this.remote_database_file) {
          document.dispatchEvent(
              new CustomEvent("xcloner_restore_display_status_text", {
                detail: {
                  status: "error",
                  message: "Please select a mysqld backup file from the list",
                },
              })
          );
          return;
        }

        jQuery(this).parent().toggleClass("cancel");

        if (!xclonerRestore.get_cancel()) {
          document.dispatchEvent(
              new CustomEvent("xcloner_restore_update_progress", {
                detail: {percent: 0, class: "determinate"},
              })
          );
          xclonerRestore.remote_restore_mysql_backup_file(
              this.remote_database_file
          );
        }
      });

  jQuery(".xcloner-restore .list-backup-content").on("click", function (e) {
    const id = jQuery(".xcloner-restore #remote_backup_file").val();

    if (id) {
      xclonerRestore.list_backup_content(id);
    }
    e.preventDefault();
  });

  jQuery("#restore_backup_to_path_form").on("submit", function (e) {
    e.preventDefault();
    xclonerRestore.set_cancel(false);
    this.backup_file = jQuery(".xcloner-restore #remote_backup_file").val();

    if (!this.backup_file) {
      document.dispatchEvent(
          new CustomEvent("xcloner_restore_display_status_text", {
            detail: {
              status: "error",
              message: "Please select a backup file from the list above",
            },
          })
      );
      return;
    }
    jQuery("#restore_backup_to_path_submit").parent().toggleClass("cancel");

    document.dispatchEvent(
        new CustomEvent("xcloner_restore_update_progress", {
          detail: {percent: 0, class: "indeterminate"},
        })
    );
    xclonerRestore.remote_restore_backup_file();
  });

  jQuery("#restore_backup_to_path_cancel").on("click", function () {
    xclonerRestore.set_cancel(true);
    jQuery(this).parent().toggleClass("cancel");
  });

  xclonerRestore.current_step = localRestore ? 2 : 0;
  xclonerRestore.next_step(0);
});


