/** global: ajaxurl */
/** global: Materialize */

class Xcloner_Manage_Backups{
		
		constructor()
		{
			this.file_counter = 0
			this.storage_selection = "";
			
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
				  data: { action : 'delete_backup_by_name', name: id, storage_selection: this.storage_selection},
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
						  
						  var files_text = [];
						  
						  for(var i in response.files)
						  {
							  
							  if(response.total_size !== undefined)
							  {
								var percent = parseInt(response.start*100)/parseInt(response.total_size)
								//jQuery("#backup_cotent_modal .progress .determinate").css('width', percent + "%")
							  }
							
							$this.file_counter++
							
							files_text[i] = "<li>"+($this.file_counter +". <span title='"+response.files[i].mtime+"'>"+response.files[i].path+"</span> ("+response.files[i].size+" bytes)")+"</li>";
						  }

						  jQuery("#backup_cotent_modal .modal-content .files-list").prepend(files_text.reverse().join("\n"));
						  
						  if(!response.finished && jQuery('#backup_cotent_modal').is(':visible'))
						  {
							$this.list_backup_content_callback(backup_file, response.start, response.part)
						  }
						  else
						  {
							jQuery("#backup_cotent_modal .progress > div").addClass('determinate').removeClass(".indeterminate").css('width', "100%")
						  }
						  
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
			jQuery('.col select').material_select();
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
		
		copy_remote_to_local(backup_file)
		{
			jQuery("#local_storage_upload_modal").modal('open');
			jQuery("#local_storage_upload_modal .modal-content .backup-name").text(backup_file);
			jQuery("#local_storage_upload_modal .status-text").removeClass("error").text("");
			jQuery("#local_storage_upload_modal .status .progress .indeterminate").removeClass("determinate").css("width", "0%");
			
			if(backup_file)
			{
				jQuery.ajax({
				  url: ajaxurl,
				  method: 'post',
				  data: { action : 'copy_backup_remote_to_local', file: backup_file, storage_type: this.storage_selection},
				  success: function(response){
					  if(response.error)
					  {
						jQuery("#local_storage_upload_modal .status-text").addClass("error").text(response.message)
					  }else{
						jQuery("#local_storage_upload_modal .status-text").removeClass("error").text("done")
					  }
					  
					  jQuery("#local_storage_upload_modal .status .progress .indeterminate").addClass("determinate").css("width", "100%");
				  },
				  error: function(xhr, textStatus, error){
				      jQuery("#local_storage_upload_modal .status-text").addClass("error").text(textStatus+error)
				  },
				  dataType: 'json'
				});
			}
			
		}
		
	//end class
	}
	
jQuery(document).ready(function(){
	
	var xcloner_manage_backups = new Xcloner_Manage_Backups();
	
	xcloner_manage_backups.storage_selection = getUrlParam('storage_selection');
	
	jQuery("a.expand-multipart").on("click", function(){
		jQuery(this).parent().find("ul.multipart").toggle();
		jQuery(this).parent().find("a.expand-multipart.remove").toggle();
		jQuery(this).parent().find("a.expand-multipart.add").toggle();
		})
	var dataTable = jQuery('#manage_backups').DataTable( {
			'responsive': true,
			'bFilter': true,
			"order": [[ 1, "desc" ]],
			buttons: [
				'selectAll',
				'selectNone'
			],
			"language": {
				"emptyTable": "No backups available",
		        buttons: {
		            selectAll: "Select all items",
		            selectNone: "Select none"
		        }
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
			"oLanguage": { 
				"sSearch": "",  
				"sSearchPlaceholder" : 'Search Backups',
				} ,
			//"ajax": ajaxurl+"?action=get_backup_list",	
		    "fnDrawCallback": function( oSettings ) {

					jQuery(this).off("click", ".delete").on("click", ".delete", function(e){

						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						var data = "";
						
						if(show_delete_alert)
						{
							if(confirm('Are you sure you want to delete it?'))
							{
								xcloner_manage_backups.delete_backup_by_name(id, (this), dataTable);
							}
						}else{
							xcloner_manage_backups.delete_backup_by_name(id, (this), dataTable);
						}
						
						
						e.preventDefault();	
					})
				
					jQuery(this).off("click", ".download").on("click", ".download", function(e){
						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						xcloner_manage_backups.download_backup_by_name(id);
						e.preventDefault();
					})

					jQuery(this).off("click", ".cloud-upload").on("click", ".cloud-upload", function(e){
						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						xcloner_manage_backups.cloud_upload(id);
						e.preventDefault();
					})
					
					jQuery(this).off("click", ".copy-remote-to-local").on("click", ".copy-remote-to-local", function(e){
						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						xcloner_manage_backups.copy_remote_to_local(id);
						e.preventDefault();
					})

					jQuery(this).off("click", ".list-backup-content").on("click", ".list-backup-content", function(e){
						var hash = jQuery(this).attr('href');
						var id = hash.substr(1)
						xcloner_manage_backups.list_backup_content(id);
						e.preventDefault();
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
	
	jQuery("#remote_storage_modal").modal();
	jQuery("#local_storage_upload_modal").modal();
	
	jQuery("#storage_selection").on("change", function(){
		console.log(jQuery(this).val());
		window.location = window.location.href.split('&storage_selection')[0]+"&storage_selection="+jQuery(this).val();
	})
	
	
	var show_delete_alert=1;
	
	
});
