/** global: XCLONER_AJAXURL */
/** global: Materialize */
/** global: dataTable */

import {doShortText} from './xcloner-admin';

var edit_schedule_modal_instance;

document.addEventListener('DOMContentLoaded', function() {
    var Modalelem = document.querySelector('#edit_schedule');
    edit_schedule_modal_instance = M.Modal.init(Modalelem);
});

jQuery(document).ready(function () {
  
  class Xcloner_Scheduler {
    constructor() {
     this.edit_modal = jQuery("#edit_schedule");
     
    }

    get_form_params() {}

    get_schedule_by_id(id) {
      var $this = this;

      if (id) {
        jQuery.ajax({
          url: XCLONER_AJAXURL,
          data: { action: "get_schedule_by_id", id: id},
          success: function (response) {
            if (response.id == id) {
              $this.create_modal(response);
            }
          },
          dataType: "json",
        });
      }
    }

    delete_schedule_by_id(id, elem, dataTable) {
      var $this = this;

      if (id) {
        jQuery.ajax({
          url: XCLONER_AJAXURL,
          data: { action: "delete_schedule_by_id", id: id },
          success: function (response) {
            //window.location = "";
            //console.log('schedule deleted');
            //alert("Schedule deleted");
            jQuery(elem).parents("tr").remove();
            dataTable.row(jQuery(elem).parents("tr")).remove().draw();
          },
          dataType: "json",
        });
      }
    }

    create_modal(response) {
      this.edit_modal.find("#schedule_id").text(response.id);

      if (response.status == 1) {
        this.edit_modal.find("#status").attr("checked", "checked");
      } else {
        this.edit_modal.find("#status").removeAttr("checked");
      }

      this.edit_modal.find("#schedule_id").text(response.id);
      this.edit_modal.find("#schedule_id_hidden").val(response.id);
      this.edit_modal.find("#schedule_name").val(response.name);
      this.edit_modal
        .find("#backup_name")
        .val(response.backup_params.backup_name);
      this.edit_modal
        .find("#email_notification")
        .val(response.backup_params.email_notification);
      this.edit_modal
        .find("#diff_start_date")
        .val(response.backup_params.diff_start_date);
      this.edit_modal
        .find('#schedule_frequency>option[value="' + response.recurrence + '"]')
        .prop("selected", true);
      this.edit_modal
        .find('#backup_type>option[value="' + response.backup_type + '"]')
        .prop("selected", true);
      this.edit_modal
        .find(
          '#schedule_storage>option[value="' + response.remote_storage + '"]'
        )
        .prop("selected", true);
      //var date = new Date(response.start_at);
      this.edit_modal.find("#schedule_start_date").val(response.start_at);

      if (
        response.backup_params.backup_encrypt !== undefined &&
        response.backup_params.backup_encrypt == 1
      ) {
        this.edit_modal.find("#backup_encrypt").attr("checked", "checked");
      }

      if (
        response.backup_params.backup_delete_after_remote_transfer !== undefined &&
        response.backup_params.backup_delete_after_remote_transfer == 1
      ) {
        this.edit_modal.find("#backup_delete_after_remote_transfer").attr("checked", "checked");
      }

      var tables = jQuery.parseJSON(response.table_params);

      var tables_list = "";

      for (var db in tables) {
        for (var i in tables[db])
          tables_list += db + "." + tables[db][i] + "\n";
      }

      this.edit_modal.find("#table_params").val(tables_list);

      var files = jQuery.parseJSON(response.excluded_files);
      var exclude_files_list = "";
      for (var i in files) {
        exclude_files_list += files[i] + "\n";
      }

      this.edit_modal.find("#excluded_files").val(exclude_files_list);

      jQuery(".col select").formSelect();

      M.updateTextFields();

      edit_schedule_modal_instance.open();
    }

    save_schedule(form, dataTable) {
      var data = jQuery(form).serialize();
      var $this = this;

      jQuery
        .ajax({
          url: XCLONER_AJAXURL,
          dataType: "json",
          type: "POST",
          data: data,
          error: function (err) {
            //show_ajax_error("Communication Error", "", err)
            //console.log(err);
            alert("Error saving schedule!");
          },
        })
        .done(function (json) {
          if (json.error !== undefined) {
            alert("Error saving schedule!" + json.error);
            return;
          }

          edit_schedule_modal_instance.close();
          //location.reload();
          dataTable.ajax.reload();
        });
    }

    IsJsonString(str) {
      try {
        JSON.parse(str);
      } catch (e) {
        return false;
      }
      return true;
    }

    //end class
  }

  var xcloner_scheduler = new Xcloner_Scheduler();

  jQuery("select[required]").css({
    display: "block",
    height: 0,
    padding: 0,
    width: 0,
    position: "absolute",
  });

  let dataTable = jQuery("#scheduled_backups").DataTable({
    responsive: true,
    bFilter: false,
    order: [[3, "desc"]],
    buttons: ["selectAll", "selectNone"],
    language: {
      emptyTable: "No schedules available",
      buttons: {
        selectAll: "Select all items",
        selectNone: "Select none",
      },
    },
    columnDefs: [
      { targets: "no-sort", orderable: false },
      { className: "hide-on-med-and-down", targets: [3, 5] },
    ],
    ajax: XCLONER_AJAXURL + "&action=get_scheduler_list",
    fnDrawCallback: function (oSettings) {
      jQuery(this)
        .off("click", ".edit")
        .on("click", ".edit", function () {
          var hash = jQuery(this).attr("href");
          var id = hash.substr(1);
          var data = xcloner_scheduler.get_schedule_by_id(id);
        });

      jQuery(this)
        .off("click", ".delete")
        .on("click", ".delete", function () {
          var hash = jQuery(this).attr("href");
          var id = hash.substr(1);
          if (confirm("Are you sure you want to delete it?")) {
            var data = xcloner_scheduler.delete_schedule_by_id(
              id,
              this,
              dataTable
            );
          }
        });

      jQuery("span.shorten_string").each(function () {
        doShortText(jQuery(this));
      });
      jQuery("span.shorten_string").click(function () {
        jQuery(this).toggleClass("full");
        doShortText(jQuery(this));
      });
    },
  });

  jQuery("#save_schedule").on("submit", function () {
    xcloner_scheduler.save_schedule(jQuery(this), dataTable);

    return false;
  });

  var date_picker;
  var date_picker_allowed;

  if (typeof jQuery(".timepicker").pickatime === "function") {
    jQuery(".timepicker").pickatime({
      default: "now",
      min: [7, 30],
      twelvehour: false, // change to 12 hour AM/PM clock from 24 hour
      donetext: "OK",
      autoclose: false,
      vibrate: true, // vibrate the device when dragging clock hand
    });
  }

  if (typeof jQuery(".timepicker").pickadate === "function") {
    date_picker = jQuery(".datepicker").pickadate({
      format: "d mmmm yyyy",
      selectMonths: true, // Creates a dropdown to control month
      selectYears: 15, // Creates a dropdown of 15 years to control year
      min: +0.1,
      onSet: function () {
        //this.close();
      },
    });

    date_picker_allowed = jQuery(".datepicker_max_today").pickadate({
      format: "yyyy-mm-dd",
      selectMonths: true, // Creates a dropdown to control month
      selectYears: 15, // Creates a dropdown of 15 years to control year
      max: +0.1,
      onSet: function () {
        //this.close();
      },
    });
  }
});
