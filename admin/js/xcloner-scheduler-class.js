jQuery(document).ready(function(){

	class Xcloner_Scheduler{
		
		constructor()
		{
			this.edit_modal = jQuery('.modal').modal();
		}
		
		get_form_params()
		{
			
		}
		
		get_schedule_by_id(id)
		{
			var $this = this
			
			if(id){
				jQuery.ajax({
				  url: ajaxurl,
				  data: { action : 'get_schedule_by_id', id: id},
				  success: function(response){
					  if(response.id == id)
						$this.create_modal(response)
					  },
				  dataType: 'json'
				});
			}
		}
		
		delete_schedule_by_id(id, elem, dataTable)
		{
			var $this = this
			
			if(id){
				jQuery.ajax({
				  url: ajaxurl,
				  data: { action : 'delete_schedule_by_id', id: id},
				  success: function(response){
					  //window.location = "";
					  //alert("Schedule deleted");
					  dataTable
				        .row( jQuery(elem).parents('tr') )
				        .remove()
				        .draw();
					  },
				  dataType: 'json'
				});
			}
		}
		
		create_modal(response)
		{
			this.edit_modal.find("#schedule_id").text(response.id)
			
			if(response.status == 1)
				this.edit_modal.find("#status").attr("checked", "checked");
			else	
				this.edit_modal.find("#status").removeAttr("checked");
				
			this.edit_modal.find("#schedule_id").text(response.id)
			this.edit_modal.find("#schedule_id_hidden").val(response.id)
			this.edit_modal.find("#schedule_name").val(response.name)
			this.edit_modal.find("#backup_name").val(response.backup_params.backup_name)
			this.edit_modal.find("#email_notification").val(response.backup_params.email_notification)
			this.edit_modal.find('#schedule_frequency>option[value="' + response.recurrence + '"]').prop('selected', true);
			//var date = new Date(response.start_at);
			this.edit_modal.find("#schedule_start_date").val(response.start_at)
			this.edit_modal.find("#table_params").val(response.table_params)
			this.edit_modal.find("#excluded_files").val(response.excluded_files)
			
			jQuery('select').material_select();
			
			Materialize.updateTextFields();
			
			this.edit_modal.modal('open');
		}
		
		save_schedule(form, dataTable)
		{
			if(!this.IsJsonString(jQuery("#table_params").val()) )
			{
				alert("Database field is not a valid json data!");
				return false;
			}
			
			if(!this.IsJsonString(jQuery("#excluded_files").val()) )
			{
				alert("Exclude files field is not a valid json data!");
				return false;
			}
			
			var data = jQuery(form).serialize();
			var $this = this
			
			jQuery.ajax({
				url: ajaxurl,
				dataType: 'json',
				type: 'POST',
				data: data,
				error: function(err) {
					//show_ajax_error("Communication Error", "", err)
					//console.log(err);
					alert("Error saving schedule!");
				}
			}).done(function(json) {

				if(json.error !== undefined){
					alert("Error saving schedule!"+json.error);
					return;
				}
				
				$this.edit_modal.modal('close');
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
	
	jQuery("select[required]").css({display: "block", height: 0, padding: 0, width: 0, position: 'absolute'});
	
    dataTable = jQuery('#scheduled_backups').DataTable( {
		'responsive': true,
		'bFilter': false,
		"order": [[ 3, "desc" ]],
		buttons: [
			'selectAll',
			'selectNone'
		],
		"language": {
				"emptyTable": "No schedules available"
		},
		columnDefs: [
			{ targets: 'no-sort', orderable: false }
		],
		language: {
	        buttons: {
	            selectAll: "Select all items",
	            selectNone: "Select none"
	        }
	    },
	    "ajax": ajaxurl+"?action=get_scheduler_list",
	    "fnDrawCallback": function( oSettings ) {
			jQuery("#scheduled_backups").find(".edit").each(function(){
				jQuery(this).off("click").on("click", function(){
					var hash = jQuery(this).attr('href');
					var id = hash.substr(1)
					var data = xcloner_scheduler.get_schedule_by_id(id);
				})
			})
			
			jQuery("#scheduled_backups").find(".delete").each(function(){
				jQuery(this).off("click").on("click", function(){
					var hash = jQuery(this).attr('href');
					var id = hash.substr(1)
					if(confirm('Are you sure you want to delete it?'))
						var data = xcloner_scheduler.delete_schedule_by_id(id, (this), dataTable);
				})
			})
			
		}
	});
	
	jQuery("#save_schedule").on("submit", function(){

		xcloner_scheduler.save_schedule(jQuery(this), dataTable)
		
		return false;
	})
	
	jQuery('.timepicker').pickatime({
	    default: 'now',
	    min: [7,30],
	    twelvehour: false, // change to 12 hour AM/PM clock from 24 hour
	    donetext: 'OK',
		autoclose: false,
		vibrate: true // vibrate the device when dragging clock hand
	});

	var date_picker = jQuery('.datepicker').pickadate({
		format: 'd mmmm yyyy',
		selectMonths: true, // Creates a dropdown to control month
		selectYears: 15, // Creates a dropdown of 15 years to control year
		min: +0.1,
		onSet: function() {
			//this.close();
		}
	});
	
});
