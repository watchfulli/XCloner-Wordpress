
jQuery(document).ready(function(){

	class Xcloner_Manage_Backups{
		
		constructor()
		{
			//this.edit_modal = jQuery('.modal').modal();
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
		
		download_backup_by_name(id, elem, dataTable)
		{
			window.open(ajaxurl+"?action=download_backup_by_name&name="+id);
			return false;
		}
		
		delete_backup_by_name(id, elem, dataTable)
		{
			var $this = this
			
			if(id){
				jQuery.ajax({
				  url: ajaxurl,
				  data: { action : 'delete_backup_by_name', name: id},
				  success: function(response){
					  if(response.finished)
					  {
						dataTable
							.row( jQuery(elem).parents('tr') )
							.remove()
							.draw();
						}
						else{
							alert("There was an error deleting the file");
						}
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
		
	//end class
	}
	
	
	var xcloner_manage_backups = new Xcloner_Manage_Backups();
	
	jQuery("a.expand-multipart").on("click", function(){
		jQuery(this).parent().find("ul.multipart").toggle();
		jQuery(this).parent().find("a.expand-multipart.remove").toggle();
		jQuery(this).parent().find("a.expand-multipart.add").toggle();
		})
	var dataTable = jQuery('#manage_backups').DataTable( {
			'responsive': true,
			'bFilter': false,
			"order": [[ 2, "desc" ]],
			buttons: [
				'selectAll',
				'selectNone'
			],
			"language": {
				"emptyTable": "No backups available"
			},
			columnDefs: [
				{ targets: 'no-sort', orderable: false }
			],
			"columns": [
			    { "width": "1%" },
			    { "width": "15%" },
			    { "width": "5%" },
			    { "width": "5%" },
			    { "width": "5%" },
			  ],
			language: {
		        buttons: {
		            selectAll: "Select all items",
		            selectNone: "Select none"
		        }
		    },
		    "fnDrawCallback": function( oSettings ) {

				jQuery("#manage_backups").find(".delete").each(function(){
					jQuery(this).off("click").on("click", function(){
						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						if(show_delete_alert && confirm('Are you sure you want to delete it?'))
							var data = xcloner_manage_backups.delete_backup_by_name(id, (this), dataTable);
						else	
							var data = xcloner_manage_backups.delete_backup_by_name(id, (this), dataTable);
					})
				})
				
				jQuery("#manage_backups").find(".download").each(function(){
					jQuery(this).off("click").on("click", function(e){
						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						var data = xcloner_manage_backups.download_backup_by_name(id, (this), dataTable);
						
					})
				})
				
			}
		});
	
	jQuery('#select_all').click(function () {    
	     jQuery('input:checkbox').prop('checked', this.checked);    
	 });
	 
	 jQuery(".delete-all").click(function(){
		if(confirm('Are you sure you want to delete selected items?'))
		{
			show_delete_alert = 0;
			jQuery('input:checkbox').each(function(){
				if(jQuery(this).is(":checked"))
				{
					jQuery(this).parent().parent().parent().find(".delete").trigger('click');
				}
			})	
			show_delete_alert = 1;
		}
	})
	
	jQuery("#save_schedule").on("submit", function(){

		xcloner_scheduler.save_schedule(jQuery(this), dataTable)
		
		return false;
	})
	
	var show_delete_alert=1;
	
	
});
