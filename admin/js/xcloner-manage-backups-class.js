
	class Xcloner_Manage_Backups{
		
		constructor()
		{
			this.file_counter = 0
			//this.edit_modal = jQuery('.modal').modal();
		}
		
		download_backup_by_name(id)
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
				  method: 'post',
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
		
		list_backup_content_callback(backup_file, start = 0, part= 0)
		{
			var $this = this;
			
			if(backup_file)
				{
					jQuery.ajax({
					  url: ajaxurl,
					  method: 'post',
					  data: { action : 'list_backup_files', file: backup_file, start: start, part: part},
					  success: function(response){
						  
						  if(response.error)
						  {
							jQuery("#backup_cotent_modal .files-list").addClass("error").prepend(response.message)
							jQuery("#backup_cotent_modal .progress > div").addClass("determinate").removeClass(".indeterminate").css('width', "100%")
							return;
						  }
						  
						  for(var i in response.files)
						  {
							  if(response.total_size !== undefined)
							  {
								var percent = parseInt(response.start*100)/parseInt(response.total_size)
								//jQuery("#backup_cotent_modal .progress .determinate").css('width', percent + "%")
							  }
							
							$this.file_counter++
							
							jQuery("#backup_cotent_modal .modal-content .files-list").prepend($this.file_counter +". <span title='"+response.files[i].mtime+"'>"+response.files[i].path+"</span> ("+response.files[i].size+" bytes)<br />\n");
						  }
						  
						  if(!response.finished && jQuery('#backup_cotent_modal').is(':visible'))
							$this.list_backup_content_callback(backup_file, response.start, response.part)
						  else
							jQuery("#backup_cotent_modal .progress > div").addClass('determinate').removeClass(".indeterminate").css('width', "100%")
						  
					  },
					  error: function(xhr, textStatus, error){
					      jQuery("#backup_cotent_modal .files-list").addClass("error").prepend(textStatus+error)
					  },
					  dataType: 'json'
					});
				}
			
		}
		
				
		list_backup_content(backup_file)
		{
			this.file_counter = 0
			jQuery("#backup_cotent_modal .modal-content .files-list").text("").removeClass("error");
			jQuery("#backup_cotent_modal .modal-content .backup-name").text(backup_file);
			jQuery("#backup_cotent_modal").modal('open');
			jQuery("#backup_cotent_modal .progress > div").removeClass('determinate').addClass("indeterminate");
			
			this.list_backup_content_callback(backup_file)
		}
		
		cloud_upload(backup_file)
		{
			jQuery('#remote_storage_modal').find(".backup_name").text(backup_file)
			jQuery('#remote_storage_modal').find("input.backup_name").val(backup_file)
			Materialize.updateTextFields();	
			jQuery('select').material_select();
			jQuery("#remote_storage_modal").modal('open')
			jQuery("#remote_storage_modal .status").hide();
			
			jQuery(".remote-storage-form").off("submit").on("submit",function(){
				jQuery("#remote_storage_modal .status").show();
				jQuery("#remote_storage_modal .status .progress .indeterminate").removeClass("determinate").css("width", "0%");
				jQuery("#remote_storage_modal .status-text").removeClass("error").text("");
				
				var storage_type = jQuery("#remote_storage_modal select").val();
				
				if(backup_file)
				{
					jQuery.ajax({
					  url: ajaxurl,
					  method: 'post',
					  data: { action : 'upload_backup_to_remote', file: backup_file, storage_type: storage_type},
					  success: function(response){
						  if(response.error)
						  {
							jQuery("#remote_storage_modal .status-text").addClass("error").text(response.message)
						  }else{
							jQuery("#remote_storage_modal .status-text").removeClass("error").text("done")
						  }
						  
						  jQuery("#remote_storage_modal .status .progress .indeterminate").addClass("determinate").css("width", "100%");
					  },
					  error: function(xhr, textStatus, error){
					      jQuery("#remote_storage_modal .status-text").addClass("error").text(textStatus+error)
					  },
					  dataType: 'json'
					});
				}
				
				return false;
			})
		}
		
	//end class
	}
	
jQuery(document).ready(function(){
	
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
			    { "width": "25%" },
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
						var data = xcloner_manage_backups.download_backup_by_name(id);
						
					})
				})
				
				jQuery("#manage_backups").find(".cloud-upload").each(function(){
					jQuery(this).off("click").on("click", function(e){
						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						var data = xcloner_manage_backups.cloud_upload(id);
						
					})
				})
				
				jQuery("#manage_backups").find(".list-backup-content").each(function(){
					jQuery(this).off("click").on("click", function(e){
						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						var data = xcloner_manage_backups.list_backup_content(id);
						
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
	
	jQuery("#remote_storage_modal").modal();
	
	
	var show_delete_alert=1;
	
	
});
