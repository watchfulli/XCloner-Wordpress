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
			this.edit_modal.find("#schedule_id").text(response.id)
			this.edit_modal.find("#schedule_id_hidden").val(response.id)
			this.edit_modal.find("#schedule_name").val(response.name)
			this.edit_modal.find('#schedule_frequency>option[value="' + response.recurrence + '"]').prop('selected', true);
			this.edit_modal.find("#start_at").val(response.start_at)
			this.edit_modal.find("#params").val(response.params)

			this.edit_modal.modal('open');
			
			jQuery('select').material_select();
		}
		save_schedule(form)
		{
			if(!this.IsJsonString(jQuery("#params").val()))
			{
				alert("Params field is not a valid json data!");
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
		"order": [[ 2, "desc" ]],
		buttons: [
			'selectAll',
			'selectNone'
		],
		language: {
	        buttons: {
	            selectAll: "Select all items",
	            selectNone: "Select none"
	        }
	    }
	});
	
	jQuery("#save_schedule").on("submit", function(){

		xcloner_scheduler.save_schedule(jQuery(this))
		
		return false;
	})
	
	jQuery("#scheduled_backups .edit").on("click", function(){
		var hash = jQuery(this).attr('href');
		var id = hash.substr(1)
		var data = xcloner_scheduler.get_schedule_by_id(id);
	})
	
	jQuery("#scheduled_backups .delete").on("click", function(){
		var hash = jQuery(this).attr('href');
		var id = hash.substr(1)
		var data = xcloner_scheduler.delete_schedule_by_id(id, (this), dataTable);
		
		
	})

});
