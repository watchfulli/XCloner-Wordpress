class Xcloner_Backup{
	
	constructor()
	{
		this.cancel = 0;
	}
	
	get_form_params()
	{
		var table_params = []
		var files_params = []
		
		jQuery.each(jQuery("#jstree_database_container").jstree("get_checked",true),function(){
			
			var object = new Object();
			object.id = this.id
			object.parent = this.parent
			
			var index = table_params.length;
			table_params[index] = object
		});
			
		jQuery.each(jQuery("#jstree_files_container").jstree("get_checked",true),function(){
			//console.log(this.id+"-"+this.parent);
			
			var object = new Object();
			object.id = this.id
			object.parent = this.parent
			
			var index = files_params.length;
			files_params[index] = object
		});
		
		var $return = new Object();
		$return.table_params = table_params;
		$return.files_params = files_params;
		$return.backup_params = jQuery('#generate_backup_form').serializeArray();
		
		return $return;
	}
	
	
	do_filesystem_scan_callback(json, params, callback)
	{
		if(json.total_files_num)
			jQuery(".file-system .file-counter").text(parseInt(json.total_files_num) + parseInt(jQuery(".file-system .file-counter").text()));
		
		if(json.total_files_size)	{
			var size = parseFloat(json.total_files_size) + parseFloat(jQuery(".file-system .file-size-total").text())
			jQuery(".file-system .file-size-total").text(size.toFixed(2));
		}
		
		if(json.last_logged_file)
			jQuery(".file-system .last-logged-file").text(json.last_logged_file);
		
		if(!json.finished && !this.cancel){
			
			//var xcloner_backup = new Xcloner_Backup()
			this.do_ajax('scan_filesystem', params, callback);
			return
		}
		
		this.restart_backup();
		
		//finished
		jQuery('.file-system .progress .indeterminate').removeClass('indeterminate').addClass('determinate').css('width', '100%');
		
	}
	do_filesystem_scan(elem)
	{
		jQuery(elem).show();
		jQuery(elem+' .status-body').show();
		
		jQuery(".file-system .file-counter").text(0);
		jQuery(".file-system .last-logged-file").text("");
		jQuery(".file-system .file-size-total").text(0);
		jQuery('.file-system .progress div').removeClass('determinate').addClass('indeterminate').css('width', '0%');
		
		var params = this.get_form_params();
		this.do_ajax('scan_filesystem', params, 'do_filesystem_scan_callback', 1);
		
	}
	
	cancel_backup()
	{
		this.cancel =  true;
		jQuery('#generate_backup .action-buttons a').hide();
		jQuery('#generate_backup .action-buttons .start').css('display', 'inline-block');
	}
	
	restart_backup()
	{
		this.cancel =  false;
		jQuery('#generate_backup .action-buttons a').hide();
		jQuery('#generate_backup .action-buttons .restart').css('display', 'inline-block');
	}
	
	start_backup()
	{		
			jQuery('#generate_backup .action-buttons a').hide();
			jQuery('#generate_backup .action-buttons .cancel').css('display', 'inline-block');
			jQuery('#generate_backup .backup-status').show();
			
			
			this.do_filesystem_scan("#generate_backup ul.backup-status li.file-system");
			
	}
	
	do_ajax(action, params, callback, init = 0)
	{
		var data = JSON.stringify(params);
		//console.log(JSON.stringify(params));
		var $this = this;
		
		jQuery.ajax({
			url: ajaxurl,
			dataType: 'json',
			type: 'POST',
			data: {'action': action, 'data': data, 'init': init},
			error: function(err) {
				show_ajax_error("Communication Error", "", err)
				//console.log(err);
				}
			}).done(function(json) {
				//var xcloner_backup = new Xcloner_Backup()
				$this[callback](json, params, callback)
				
			});
	}
	
	getSize(bytes, conv = 1024*1024)
	{
		return (bytes/conv).toFixed(2);
	}
}
