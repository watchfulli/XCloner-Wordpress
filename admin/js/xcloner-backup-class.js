class Xcloner_Backup{
	
	constructor()
	{
		this.cancel = 0;
		this.params;
	}
	
	get_form_params()
	{
		var table_params = []
		var files_params = []
		var extra		 = []
		
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
	
	
	do_scan_filesystem_callback(elem, action, json )
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
			this.do_ajax(elem, action);
			return false;
		}
		
		//finished
		jQuery(elem).find('.progress .indeterminate').removeClass('indeterminate').addClass('determinate').css('width', '100%');
		jQuery(".file-system .last-logged-file").text('done');
		
		//this.do_backup_database();
		this.restart_backup();
		
	}
	do_scan_filesystem()
	{
		if(this.cancel)
			return false;
			
		var elem = "#generate_backup ul.backup-status li.file-system";
		jQuery(elem).show();
		jQuery(elem+' .status-body').show();
		jQuery(elem).find('.collapsible-header').trigger('click');
		
		jQuery(".file-system .file-counter").text(0);
		jQuery(".file-system .last-logged-file").text("");
		jQuery(".file-system .file-size-total").text(0);
		jQuery('.file-system .progress div').removeClass('determinate').addClass('indeterminate').css('width', '0%');
		
		
		this.do_ajax(elem, 'scan_filesystem', 1);
		
	}
	
	do_backup_database_callback(elem, action, json )
	{
		if(json.extra)
			this.params.extra = json.extra;
		
		if(json.extra.stats)
		{	
			if(json.extra.stats.tables_count !== undefined)
				jQuery(elem).find(".table-counter").text(parseInt(json.extra.stats.tables_count));
			
			if(json.extra.stats.database_count !== undefined)
				jQuery(elem).find(".database-counter").text(parseInt(json.extra.stats.database_count));

			if(json.extra.stats.total_records !== undefined)
				jQuery(elem).find(".total-records").text(parseInt(json.extra.stats.total_records));
		}
		
		if(json.extra.tableName)
		{
			jQuery(elem).find(".last-logged-table").text(json.extra.databaseName+"."+json.extra.tableName+" ("+json.extra.processedRecords+" records)");	
		}
		
		if(json.extra.processedRecords !== undefined && !json.extra.startAtRecord && !json.extra.endDump)
		{
			var records = parseInt(jQuery(elem).find(".total-records").attr('data-processed')) + parseInt(json.extra.processedRecords);
			
			var percent = 100*parseInt(records)/parseInt(jQuery(elem).find(".total-records").text());
			jQuery(elem).find('.progress .determinate').css('width', percent+'%');
			
			jQuery(elem).find(".total-records").attr('data-processed', records);
			jQuery(elem).find(".status-body ul").prepend(jQuery("<li>").text(json.extra.databaseName+"."+json.extra.tableName+" ("+json.extra.processedRecords+" records)"));
		}
		
		if(!json.finished && !this.cancel){
			
			this.do_ajax(elem, action);
			return false;
		}
		
		jQuery(elem).find(".last-logged-table").text('done');
		this.do_scan_filesystem();
		
	}
	
	do_backup_database()
	{
		if(!jQuery('#jstree_database_container').length){
			this.do_scan_filesystem();
			return;
		}
			
		if(this.cancel)
			return false;
			
		var elem = "#generate_backup ul.backup-status li.database-backup";
		jQuery(elem).show();
		jQuery(elem+' .status-body').show();
		
		jQuery(elem).find(".total-records").text(0);
		jQuery(elem).find(".total-records").attr('data-processed', 0);
		jQuery(elem).find(".table-counter").text(0);
		jQuery(elem).find(".database-counter").text(0);
		
		jQuery(elem).find('.progress .determinate').css('width', '0%');
		
		this.do_ajax(elem, 'backup_database', 1);
	}
	
	
	cancel_backup()
	{
		this.cancel =  true;
		jQuery('#generate_backup .action-buttons a').hide();
		jQuery('#generate_backup .action-buttons .start').css('display', 'inline-block');
		
		this.restart_backup();
	}
	
	restart_backup()
	{
		jQuery('#generate_backup .action-buttons a').hide();
		jQuery('#generate_backup .action-buttons .restart').css('display', 'inline-block');
	}
	
	start_backup()
	{		
			this.cancel =  false;
			jQuery('#generate_backup .action-buttons a').hide();
			jQuery('#generate_backup .action-buttons .cancel').css('display', 'inline-block');
			jQuery('#generate_backup ul.backup-status li').hide();
			jQuery('#generate_backup .backup-status').show();
			
			this.params = this.get_form_params();
			
			this.do_backup_database();
			
	}
	
	do_ajax(elem, action, init = 0)
	{
		var callback = 'do_'+action+'_callback';
		var data = JSON.stringify(this.params);
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
				$this[callback](elem, action, json)
				
			});
	}
	
	getSize(bytes, conv = 1024*1024)
	{
		return (bytes/conv).toFixed(2);
	}
}
