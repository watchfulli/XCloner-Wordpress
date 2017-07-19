/** global: CustomEvent */
/** global: Event */

class Xcloner_Restore{

	constructor(hash)
	{
		this.steps = ['restore-script-upload-step','backup-upload-step','restore-remote-backup-step','restore-remote-database-step','restore-finish-step']
		this.ajaxurl = ajaxurl;
		this.cancel = false;
		this.upload_file_event = new Event('upload_file_event');
		this.resume = new Object();
		this.hash = hash;
		this.local_restore = 0;
		this.file_counter = 0;
		
		document.addEventListener("backup_upload_finish", function (e) {
			
			jQuery(".xcloner-restore .backup-upload-step .toggler").removeClass("cancel");

		}, false);
		
		document.addEventListener("remote_restore_backup_finish", function (e) {
			
			jQuery(".xcloner-restore .restore-remote-backup-step .toggler").removeClass("cancel");

		}, false);
		
		document.addEventListener("remote_restore_mysql_backup_finish", function (e) {
			
			jQuery(".xcloner-restore .restore-remote-database-step .toggler").removeClass("cancel");

		}, false);
		
		document.addEventListener("restore_script_invalid", function (e) {
						
			jQuery(".xcloner-restore #restore_script_url").addClass("invalid").removeClass('valid');
			jQuery(".xcloner-restore #validate_url .material-icons").text("error");
				
		}, false);
		
		document.addEventListener("restore_script_valid", function (e) {
			
			jQuery(".xcloner-restore #validate_url .material-icons").text("check_circle");
			jQuery(".xcloner-restore #restore_script_url").removeClass("invalid").addClass('valid');
				
		}, false);
		
		document.addEventListener("xcloner_populate_remote_backup_files_list", function (e) {
			
			var files = e.detail.files
			var original_value = jQuery('.xcloner-restore #remote_backup_file').val();
			
			jQuery('.xcloner-restore #remote_backup_file').find('option').not(':first').remove();
			
			
			for( var key in files)
			{
				var selected = "not-selected";
				
				if(files[key].selected || original_value == files[key].path)
				{
					selected = "selected";
				}
					
				jQuery('.xcloner-restore #remote_backup_file').append("<option value='"+files[key].path+"' "+selected+">"+files[key].path+"("+e.detail.$this.getSize(files[key].size)+" MB)"+"</option>").addClass("file");
			}
				
		}, false);
		
		document.addEventListener("xcloner_populate_remote_mysqldump_files_list", function (e) {
			
			var files = e.detail.files
			var original_value = jQuery('.xcloner-restore #remote_database_file').val();
			
			jQuery('.xcloner-restore #remote_database_file').find('option').not(':first').remove();
			
			for( var key in files)
			{
				if(files[key].selected  || original_value == files[key].path)
					var selected = "selected";
				else
					var selected = "not-selected";
					
				var option = jQuery('.xcloner-restore #remote_database_file').append("<option value='"+files[key].path+"' "+selected+">"+files[key].path+"("+e.detail.$this.getSize(files[key].size)+" MB) "+files[key].timestamp+"</option>").addClass("file");
			}
				
		}, false);
		
		document.addEventListener("xcloner_restore_next_step", function (e) {
			
			if(e.detail.$this !== undefined)
			{
				var $this = e.detail.$this
				jQuery(".xcloner-restore li."+$this.steps[$this.set_current_step]).addClass('active').show().find(".collapsible-header").trigger('click');
			}
				
		}, false);
		
		document.addEventListener("xcloner_restore_update_progress", function (e) {
			
			if(e.detail.percent !== undefined)
			{
				jQuery(".xcloner-restore .steps.active .progress").show();
				
				if(e.detail.class == "indeterminate")
				{
					jQuery(".xcloner-restore .steps.active .progress > div").addClass(e.detail.class).removeClass('determinate')
				}
				
				if(e.detail.class == "determinate")
				{
					jQuery(".xcloner-restore .steps.active .progress > div").addClass(e.detail.class).removeClass('indeterminate')
				}
				
				if(e.detail.percent == 100)
				{
					jQuery(".xcloner-restore .steps.active .progress > div").removeClass('indeterminate').addClass('determinate').css("width", e.detail.percent+"%")	
				}
				else
				{	
					jQuery(".xcloner-restore .steps.active .progress .determinate").css("width", e.detail.percent+"%")
				}
			}
				
		}, false);
		
		
		document.addEventListener("xcloner_restore_display_status_text", function (e) {
			
			if(e.detail.status === undefined)
				e.detail.status = "updated";
				
			if(e.detail.message !== undefined)
			{
				jQuery(".xcloner-restore .steps.active .status").html("<div class='"+e.detail.status+"'>"+e.detail.message+"</div>");
			}
				
		}, false);
		
		document.addEventListener("xcloner_populate_remote_restore_path", function (e) {
			
			//dir: response.statusText.dir, restore_script_url: response.statusText.restore_script_url
			if(e.detail.dir !== undefined)
			{
				if(!jQuery(".xcloner-restore #remote_restore_path").val())
				{
					jQuery(".xcloner-restore #remote_restore_path").val(e.detail.dir);
				}
			}
			
			if(e.detail.restore_script_url !== undefined)
			{
				if(!jQuery(".xcloner-restore #remote_restore_url").val())
				{
					jQuery(".xcloner-restore #remote_restore_url").val(e.detail.restore_script_url);
				}
				
				if(!jQuery(".xcloner-restore #remote_restore_site_url").val())
				{
					jQuery(".xcloner-restore #remote_restore_site_url").val(e.detail.restore_script_url);	
				}
				
				if(!jQuery(".xcloner-restore #remote_mysql_host").val())
				{
					jQuery(".xcloner-restore #remote_mysql_host").val(e.detail.remote_mysql_host);
				}
				if(!jQuery(".xcloner-restore #remote_mysql_db").val())
				{
					jQuery(".xcloner-restore #remote_mysql_db").val(e.detail.remote_mysql_db);
				}
				if(!jQuery(".xcloner-restore #remote_mysql_user").val())
				{
					jQuery(".xcloner-restore #remote_mysql_user").val(e.detail.remote_mysql_user);
				}
				
				if(!jQuery(".xcloner-restore #remote_mysql_pass").val())
				{
					jQuery(".xcloner-restore #remote_mysql_pass").val(e.detail.remote_mysql_pass);
				}	
				
			}
				
		}, false);
		
		document.addEventListener("remote_restore_update_files_list", function (e) {
			
			if(e.detail.files !== undefined && e.detail.files.length)
			{
				var files_text = [];
				for(var i=0; i<e.detail.files.length;i++)
				{
					files_text[i] = "<li>"+(e.detail.files[i])+"</li>";
				}
				if(!jQuery('.xcloner-restore .restore-remote-backup-step .files-list').is(":hidden"))
				{
					jQuery('.xcloner-restore .restore-remote-backup-step .files-list').prepend(files_text.reverse().join("\n"));
				}
			}else if(!jQuery.isArray(e.detail.files)){
				jQuery('.xcloner-restore .restore-remote-backup-step .files-list').html("");
			}
				
		}, false);
		
		document.addEventListener("xcloner_restore_display_query_box", function (e) {
			
			if(e.detail.query)
			{
				jQuery(".xcloner-restore .query-box").show();
				jQuery(".xcloner-restore .query-list").val(e.detail.query)
			}else{
				jQuery(".xcloner-restore .query-box").hide();
				jQuery(".xcloner-restore .query-list").val("")
			}
			
				
		}, false);
		
		document.addEventListener("xcloner_restore_finish", function (e) {
			
			jQuery(".xcloner-restore #xcloner_restore_finish").show();
			jQuery(".xcloner-restore #open_target_site a").removeAttr("disabled");
				
		}, false);
		
		
	}
	
	get_remote_backup_files_callback(response, status, params = new Object())
	{
		if(status)
		{
			var files = response.statusText.files;
			document.dispatchEvent(new CustomEvent("xcloner_populate_remote_backup_files_list", {detail: {files: files, $this: this }}));
		}
	}
	
	get_remote_backup_files()
	{
		this.ajaxurl = this.restore_script_url;
		this.set_cancel(false);
		
		var params = new Object()
		params.local_backup_file = jQuery(".xcloner-restore .backup-upload-step #backup_file").val();
		
		this.do_ajax('get_remote_backup_files_callback', 'list_backup_archives', params)
		
		this.get_remote_restore_path_default()
	}
	
	get_remote_mysqldump_files_callback(response, status, params = new Object())
	{
		
		if(status)
		{
			var files = response.statusText.files;
			document.dispatchEvent(new CustomEvent("xcloner_populate_remote_mysqldump_files_list", {detail: {files: files, $this: this }}));
		}
	}
	
	get_remote_mysqldump_files()
	{
		this.ajaxurl = this.restore_script_url;
		this.set_cancel(false);
		
		
		if(this.resume.callback == "get_remote_mysqldump_files_callback")
		{
			//console.log("do resume");
			this.do_ajax(this.resume.callback, this.resume.action, this.resume.params);
			this.resume = new Object();
			return;
		}
		
		
		var params = new Object()
		params.backup_file = this.get_backup_file()
		params.remote_path = this.get_remote_path()
		
		//console.log(params)
		
		this.do_ajax('get_remote_mysqldump_files_callback', 'list_mysqldump_backups', params)
		
	}
	
	get_backup_file()
	{
		return jQuery(".xcloner-restore #remote_backup_file").val()
	}
	
	get_remote_path()
	{
		return jQuery(".xcloner-restore #remote_restore_path").val()
	}
	
	get_remote_restore_path_default_callback(response, status, params = new Object())
	{
		if(status)
		{
			document.dispatchEvent(new CustomEvent("xcloner_populate_remote_restore_path", {detail: response.statusText}))
		}
	}
	
	get_remote_restore_path_default()
	{
		this.ajaxurl = this.restore_script_url;
		this.set_cancel(false);
		
		var params = new Object()
		
		params.restore_script_url = this.restore_script_url;
		
		this.do_ajax('get_remote_restore_path_default_callback', 'get_current_directory', params)
	}
	
	remote_restore_backup_file_callback(response, status, params = new Object())
	{
		
		if(!status)
		{
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: response.status+" "+response.statusText }}));
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
			document.dispatchEvent(new CustomEvent("remote_restore_backup_finish"));
			return;
		}
		
		var processed = parseInt(response.statusText.start)+parseInt(response.statusText.processed)
		
		if(response.statusText.extracted_files)
		{
			document.dispatchEvent(new CustomEvent("remote_restore_update_files_list", {detail: {files: response.statusText.extracted_files}}));
		}
			
		if(!response.statusText.finished)
		{
			params.start = response.statusText.start
			params.part = response.statusText.part
			params.processed = response.statusText.processed
			
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: 'Processing <strong>'+response.statusText.backup_file+'</strong>- processed '+this.getSize(processed, 1024)+" KB from archive"}}));
			
			this.do_ajax('remote_restore_backup_file_callback', 'restore_backup_to_path', params)
			return
		}
		
		document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
		document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: "Done restoring <strong>"+ response.statusText.backup_file +"</strong>."}}));
		document.dispatchEvent(new CustomEvent("remote_restore_backup_finish"));
		this.cancel = false;
	}
	
	remote_restore_backup_file(backup_file, remote_path)
	{
		this.ajaxurl = this.restore_script_url;
		this.set_cancel(false);
		
		var params 			= new Object()
		params.backup_file 	= backup_file
		params.remote_path 	= remote_path
		params.filter_files = jQuery(".xcloner-restore input[name=filter_files]:checked").val()
		
		if(this.resume.callback == "remote_restore_backup_file_callback")
		{
			//console.log("do resume");
			this.do_ajax(this.resume.callback, this.resume.action, this.resume.params);
			this.resume = new Object();
			return;
		}
		document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0, class: 'indeterminate' }}));
		document.dispatchEvent(new CustomEvent("remote_restore_update_files_list", {detail: {files: ""}}));
		
		this.do_ajax('remote_restore_backup_file_callback', 'restore_backup_to_path', params)
	}
	
	remote_restore_mysql_backup_file_callback(response, status, params = new Object())
	{
		if(!status)
		{
			this.start = response.statusText.start;
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_query_box", {detail: { query: response.statusText.query }}));
			
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: response.status+" "+response.statusText.message }}));
			//document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
			document.dispatchEvent(new CustomEvent("remote_restore_mysql_backup_finish"));
			return;
		}
		
		document.dispatchEvent(new CustomEvent("xcloner_restore_display_query_box", {detail: { query: "" }}));
		params.query = "";
		
		var processed = parseInt(response.statusText.start)+parseInt(response.statusText.processed)
			
		if(!response.statusText.finished)
		{
			params.start = response.statusText.start
			params.processed = response.statusText.processed
			
			var percent = 0;
			
			if(response.statusText.backup_size)
				percent = (100*parseInt(response.statusText.start))/parseInt(response.statusText.backup_size);
			
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: 'Processing <strong>'+response.statusText.backup_file+'</strong>- wrote '+this.getSize(response.statusText.start, 1024)+" KB of data"}}));
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: percent }}));
			
			this.do_ajax('remote_restore_mysql_backup_file_callback', 'restore_mysql_backup', params)
			return
		}
		
		document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
		document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: "Done restoring <strong>"+ response.statusText.backup_file +"</strong>."}}));
		document.dispatchEvent(new CustomEvent("remote_restore_mysql_backup_finish"));
		this.cancel = false;
		
	}
	
	remote_restore_mysql_backup_file(mysqldump_file)
	{
		this.ajaxurl = this.restore_script_url;
		this.set_cancel(false);
		
		var params = new Object()
		
		params.remote_mysql_host 	= jQuery(".xcloner-restore #remote_mysql_host").val();
		params.remote_mysql_db 		= jQuery(".xcloner-restore #remote_mysql_db").val();
		params.remote_mysql_user 	= jQuery(".xcloner-restore #remote_mysql_user").val();
		params.remote_mysql_pass 	= jQuery(".xcloner-restore #remote_mysql_pass").val();
		params.remote_path 			= jQuery(".xcloner-restore #remote_restore_path").val();

		params.wp_home_url 			= jQuery(".xcloner-restore #wp_home_url").val();
		params.remote_restore_url 	= jQuery(".xcloner-restore #remote_restore_url").val();
		
		if(jQuery(".xcloner-restore #wp_site_url").length)
		{
			params.wp_site_url 			= jQuery(".xcloner-restore #wp_site_url").val();
			params.restore_site_url 	= jQuery(".xcloner-restore #remote_restore_site_url").val();
		}
		
		//console.log(params)
		
		params.mysqldump_file 		= mysqldump_file
		params.query 				= ""
		params.start 			= 0
		
		if(jQuery(".xcloner-restore .query-box .query-list").val())
		{
			params.query = jQuery(".xcloner-restore .query-box .query-list").val();
			params.start = this.start
		}

		document.dispatchEvent(new CustomEvent("xcloner_restore_display_query_box", {detail: { query: "" }}));
		
		if(this.resume.callback == "remote_restore_mysql_backup_file_callback")
		{
			//console.log("do resume mysql backup restore");
			this.do_ajax(this.resume.callback, this.resume.action, this.resume.params);
			this.resume = new Object();
			return;
		}
		
		this.do_ajax('remote_restore_mysql_backup_file_callback', 'restore_mysql_backup', params)
	}
	
	restore_finish_callback(response, status, params = new Object())
	{
		if(status)
		{
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: response.statusText, $this: this }}))
		}else{
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: "error", message: response.statusText, $this: this }}))
			return false;
		}
		
		document.dispatchEvent(new CustomEvent("xcloner_restore_finish", {detail: {message: response.statusText, $this: this }}))
	}
	
	restore_finish()
	{
		this.ajaxurl = this.restore_script_url;
		this.set_cancel(false);
		
		var params = new Object()
		
		params.remote_mysql_host 	= jQuery(".xcloner-restore #remote_mysql_host").val();
		params.remote_mysql_db 		= jQuery(".xcloner-restore #remote_mysql_db").val();
		params.remote_mysql_user 	= jQuery(".xcloner-restore #remote_mysql_user").val();
		params.remote_mysql_pass 	= jQuery(".xcloner-restore #remote_mysql_pass").val();
		params.remote_path 			= jQuery(".xcloner-restore #remote_restore_path").val();
		params.remote_restore_url 	= jQuery(".xcloner-restore #remote_restore_url").val();
		
		params.delete_backup_temporary_folder 	= 0;
		params.delete_restore_script 			= 0;
		params.update_remote_site_url 			= 0;
			
		if(jQuery(".xcloner-restore #delete_backup_temporary_folder").is(":checked"))
			params.delete_backup_temporary_folder 	= 1;
		if(jQuery(".xcloner-restore #delete_restore_script").is(":checked"))
			params.delete_restore_script 			= 1;
		if(jQuery(".xcloner-restore #update_remote_site_url").is(":checked"))
			params.update_remote_site_url 			= 1;

		this.do_ajax('restore_finish_callback', 'restore_finish', params)
	
	}
	
	upload_backup_file(file)
	{
		this.ajaxurl = ajaxurl;
		var params = new Object()
		this.set_cancel(false);
		
		if(this.resume.callback == "upload_backup_file_callback")
		{
			this.do_ajax(this.resume.callback, this.resume.action, this.resume.params);
			this.resume = new Object();
			return;
		}
		
		params.file = file;
		params.start = 0;
		params.target_url = this.restore_script_url
		
		document.dispatchEvent(new CustomEvent("backup_upload_start"));
		document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: "Uploading backup 0%" }}));
		document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0 }}));
		
		this.do_ajax('upload_backup_file_callback', 'restore_upload_backup', params)
	}
	
	
	upload_backup_file_callback(response, status, params = new Object())
	{
		if(!status)
		{
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: response.status+" "+response.statusText }}));
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
			return;
		}	
		
		if(response && (response.start !== false || response.part<response.total_parts))
		{
			var percent = 0;
			if(response.total_size)
			{
				if(!response.start)
					response.start = 0;
				var size = parseInt(response.start)+parseInt(response.uploaded_size)
				percent = (100*parseInt(size))/parseInt(response.total_size)
			}
			
			var part_text = "";
			if(response.part > 0)
				part_text = "part "+response.part+" -  ";
			
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: percent }}));
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: "Uploading backup "+part_text+parseFloat(percent).toFixed(2)+"%" }}));
			
			params.start = response.start;
			params.part = response.part;
			params.uploaded_size = response.uploaded_size;
			this.do_ajax('upload_backup_file_callback', 'restore_upload_backup', params)
		}
		else
		{
			this.cancel = false
			document.dispatchEvent(new CustomEvent("backup_upload_finish"));
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: "Done." }}));
		}
	}
	
	verify_restore_url_callback(response, status, params = new Object())
	{
		if(!status)
		{
			var href_url = "<a href='"+this.restore_script_url+"' target='_blank'>restore script address</a>";
			document.dispatchEvent(new CustomEvent("restore_script_invalid"));
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status:'error', message: "Could not access the restore script: "+response.status+" "+response.statusText +". Please check the javascript console for more details. Are you able to see a valid JSON response of the "+href_url+" in your browser?" }}));
			//document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 100 }}));
			
		}else{
			
			document.dispatchEvent(new CustomEvent("restore_script_valid"));
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: "Validation ok." }}));
			
			if(this.local_restore)
			{
				this.next_step(1);
			}else{
				this.next_step();
			}
		}
	
	}
	
	verify_restore_url(response, status, params = new Object())
	{
		if(this.restore_script_url == "http://" || this.restore_script_url == "https://" || this.restore_script_url == "" || this.restore_script_url === undefined)
		{
			this.restore_script_url = ajaxurl+"?action=restore_backup";
			this.local_restore = 1;
			//jQuery("#remote-restore-options").hide();
			//jQuery("#update_remote_site_url").attr("disabled", "disabled").removeAttr("checked");
			jQuery("#delete_restore_script").attr("disabled", "disabled").removeAttr("checked");
		}else{
			this.local_restore = 0;
			//jQuery("#remote-restore-options").show();
			//jQuery("#update_remote_site_url").attr("checked", "checked").removeAttr("disabled");
			jQuery("#delete_restore_script").attr("checked", "checked").removeAttr("disabled");
		}
		
		if(this.local_restore == 1)
		{
			jQuery(".restore-remote-backup-step #filter_files_all").removeAttr("checked").attr("disabled","disabled")
			jQuery(".restore-remote-backup-step #filter_files_wp_content").attr("checked", "checked");
			
		}else{
			jQuery(".restore-remote-backup-step #filter_files_all").removeAttr("disabled").attr("checked", "checked");
			jQuery(".restore-remote-backup-step #filter_files_wp_content").removeAttr("checked");
		}
		
		this.ajaxurl = this.restore_script_url;
		
		
		jQuery(".xcloner-restore #xcloner_restore_finish").hide();
		
		this.cancel = false;
		this.set_current_step = 0
		
		this.do_ajax("verify_restore_url_callback");
			
	}
	
	open_target_site(elem)
	{
		var url = jQuery(".xcloner-restore #remote_restore_url").val();
		
		if(!url )
		{
			jQuery(".xcloner-restore #wp_home_url").val();
		}
		
		jQuery(elem).attr("href", url);
	}
	
	next_step(inc = 0)
	{
		this.set_current_step = jQuery(".xcloner-restore li.active").attr("data-step");
		
		this.set_current_step = parseInt(this.set_current_step) + parseInt(inc);
		
		document.dispatchEvent(new CustomEvent("xcloner_restore_next_step", {detail: {$this: this}}));
		
	}
	
	list_backup_content_callback(response, status, params = new Object())
	{	
		if(response.error)
		{
			jQuery("#backup_cotent_modal .files-list").addClass("error").prepend(response.statusText)
			jQuery("#backup_cotent_modal .progress > div").addClass("determinate").removeClass(".indeterminate").css('width', "100%")
			return;
		}
		
		response = response.statusText;
		
		var files_text = [];
		
		for(var i in response.files)
		{
		  
		  if(response.total_size !== undefined)
		  {
			var percent = parseInt(response.start*100)/parseInt(response.total_size)
			//jQuery("#backup_cotent_modal .progress .determinate").css('width', percent + "%")
		  }
		
		this.file_counter++
		
		files_text[i] = "<li>"+(this.file_counter +". <span title='"+response.files[i].mtime+"'>"+response.files[i].path+"</span> ("+response.files[i].size+" bytes)")+"</li>";
		}
				
		jQuery("#backup_cotent_modal .modal-content .files-list").prepend(files_text.reverse().join("\n"));
		
		if(!response.finished && jQuery('#backup_cotent_modal').is(':visible'))
		{
			//$this.list_backup_content_callback(backup_file, response.start, response.part)
			params.start = response.start
			params.part = response.part
			
			this.do_ajax("list_backup_content_callback", "list_backup_files", params);
		}
		else
		{
			jQuery("#backup_cotent_modal .progress > div").addClass('determinate').removeClass(".indeterminate").css('width', "100%")
		}
			
	}
		
				
	list_backup_content(backup_file)
	{
		this.file_counter = 0
		jQuery("#backup_cotent_modal .modal-content .files-list").text("").removeClass("error");
		jQuery("#backup_cotent_modal .modal-content .backup-name").text(backup_file);
		jQuery("#backup_cotent_modal").modal('open');
		jQuery("#backup_cotent_modal .progress > div").removeClass('determinate').addClass("indeterminate");
		
		//this.list_backup_content_callback(backup_file)
		var params = new Object()
		params.file = backup_file
		params.start = 0
		params.part = 0

		this.do_ajax("list_backup_content_callback", "list_backup_files", params);
	}
	
	init_resume()
	{
		this.resume = new Object()
		if(jQuery(".xcloner-restore .steps.active .progress").is(":visible"))
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0 }}));
		if(jQuery(".xcloner-restore .steps.active .status").html())
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {message: ""}}));
		document.dispatchEvent(new CustomEvent("remote_restore_update_files_list", {detail: {files: ""}}));
	}
	
	/** global: ajaxurl */
	do_ajax(callback, action="", params= new Object())
	{
		if(action == "restore_upload_backup")
		{
			params.action = "restore_upload_backup";
		}
		
		params.xcloner_action 	= action
		params.hash 	= this.hash
		params.API_ID 	= ID()
		
		if(this.cancel == true)
		{
			this.resume.callback = callback
			this.resume.action = action
			this.resume.params = params
			
			//this.request.abort();
			
			return;
		}
		
		if(!this.restore_script_url)
		{
			return false;
		}
		
		var $this = this;
		
		jQuery(".xcloner-restore .steps.active").addClass("active_status");
		
		this.request = jQuery.ajax({
			url: this.ajaxurl,
			dataType: 'json',
			type: 'POST',
			crossDomain: true,
			data: params,
			error: function(xhr, status, error) {
					document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: xhr.status+" "+xhr.statusText}}));
					$this[callback](xhr, false);
				}
			}).done(function(json) {
				if(!json)
				{
					document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: "Lost connection with the API, please try and authentificate again"}}));
				}
				
				if(json.status != 200){
						if(json.error)
						{
							document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: json.message}}));
						}
						else
						{
							$this[callback](json, false, params);
						}
							
						return;
				}
				$this[callback](json, true, params);
			});
	}
	
	
	set_restore_script_url(url)
	{
		this.restore_script_url = url;
	}
	
	set_current_step(id)
	{
		this.set_current_step = id;
	}
	
	set_cancel(status)
	{
		if(status)
		{
			//document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {append : true, message: "Cancelled" }}));
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0, class: 'determinate' }}));

		}
			
		this.cancel = status
	}
	
	get_cancel(status)
	{
		return this.cancel
	}
	
	getSize(bytes, conv = 1024*1024)
	{
		return (bytes/conv).toFixed(2);
	}
	
	
}

var xcloner_auth_key = "";

jQuery(document).ready(function(){
	
	var xcloner_restore = new Xcloner_Restore(xcloner_auth_key);
	
	xcloner_restore.set_current_step(0);
	
	jQuery('.col select').material_select();
	
	jQuery(".xcloner-restore .upload-backup.cancel").on("click", function(){
		//jQuery(".xcloner-restore #upload_backup").show();
		//jQuery(this).hide();
		xcloner_restore.set_cancel(true);
	})
	
	jQuery(".xcloner-restore .upload-backup").on("click",function(){
		
		if(jQuery(this).hasClass('cancel'))
			xcloner_restore.set_cancel(true);
		else
			xcloner_restore.set_cancel(false);
		
		var backup_file = jQuery(".xcloner-restore #backup_file").val();

		if(backup_file)
		{
			jQuery(this).parent().toggleClass("cancel")
			
			if(!xcloner_restore.get_cancel())
				xcloner_restore.upload_backup_file(backup_file);
		}else{
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: "Please select a backup file from the list above" }}));

		}
	})
	
	jQuery(".xcloner-restore #validate_url").on("click", function(){
		
		xcloner_restore.set_restore_script_url(jQuery(".xcloner-restore #restore_script_url").val());
		xcloner_restore.verify_restore_url();	
			
	})
	
	jQuery(".xcloner-restore #skip_upload_backup").on("click", function(){
		
		xcloner_restore.set_cancel(true);
		xcloner_restore.next_step();
			
	})
	
	jQuery(".xcloner-restore #skip_restore_remote_database_step").on("click", function(){
		
		xcloner_restore.set_cancel(true);
		xcloner_restore.next_step();
			
	})
	
	jQuery(".xcloner-restore li.steps").on("click", function(){
		xcloner_restore.set_current_step = (jQuery(this).attr("data-step")-1)
	})
	
	jQuery(".xcloner-restore #skip_remote_backup_step").on("click", function(){
		xcloner_restore.set_cancel(true);
		xcloner_restore.next_step();
	})
	
	jQuery(".xcloner-restore .restore-remote-backup-step .collapsible-header").click(function(){
		xcloner_restore.get_remote_backup_files();
	})
	
	jQuery(".xcloner-restore .restore-remote-database-step .collapsible-header").click(function(){
		xcloner_restore.get_remote_mysqldump_files();
	})
	
	jQuery(".xcloner-restore #remote_backup_file").on("change", function(){
		xcloner_restore.init_resume()
	})
	
	jQuery(".xcloner-restore #backup_file").on("change", function(){
		xcloner_restore.init_resume()
	})
	
	jQuery(".xcloner-restore #restore_finish").click(function(){
		xcloner_restore.restore_finish();
	})
	
	jQuery(".xcloner-restore #open_target_site a").click(function(){
		xcloner_restore.open_target_site(this);
	})
	
	jQuery(".xcloner-restore #refresh_remote_backup_file").on("click", function(e){
		document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0 }}));
		xcloner_restore.resume = new Object();
		xcloner_restore.get_remote_backup_files();
		e.stopPropagation();
	})
	
	jQuery(".xcloner-restore #refresh_database_file").on("click", function(e){
		document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0 }}));
		xcloner_restore.resume = new Object();
		xcloner_restore.get_remote_mysqldump_files();
		e.stopPropagation();
	})
	
	jQuery(".xcloner-restore #toggle_file_restore_display").on("click", function(e){
		jQuery(".xcloner-restore .restore-remote-backup-step .files-list").toggle();
	})
	
	jQuery(".xcloner-restore .restore_remote_mysqldump").on("click", function(e){
		if(jQuery(this).hasClass('cancel'))
			xcloner_restore.set_cancel(true);
		else
			xcloner_restore.set_cancel(false);
		
		this.remote_database_file = jQuery(".xcloner-restore #remote_database_file").val();
			
		if(!this.remote_database_file)
		{
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: "Please select a mysqld backup file from the list" }}));
			return;
		}	
		
		jQuery(this).parent().toggleClass("cancel")
			
		if(!xcloner_restore.get_cancel())
		{
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0, class: 'determinate' }}));
			xcloner_restore.remote_restore_mysql_backup_file(this.remote_database_file);
		}
		
	})
	
	jQuery(".xcloner-restore .list-backup-content").on("click", function(e){
			var id = jQuery(".xcloner-restore #remote_backup_file").val()
			
			if(id)
			{
				xcloner_restore.list_backup_content(id);
			}
			e.preventDefault();
	});
	
	jQuery(".xcloner-restore .restore-remote-backup-step .restore_remote_backup").click(function(){
		if(jQuery(this).hasClass('cancel'))
			xcloner_restore.set_cancel(true);
		else
			xcloner_restore.set_cancel(false);
		
		this.backup_file = jQuery(".xcloner-restore #remote_backup_file").val();
			
		if(!this.backup_file)
		{
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: "Please select a backup file from the list above" }}));
			return;
		}	
		
		this.remote_path = jQuery(".xcloner-restore #remote_restore_path").val();
			
		if(!this.remote_path)
		{
			document.dispatchEvent(new CustomEvent("xcloner_restore_display_status_text", {detail: {status: 'error', message: "Please enter the remote restore path" }}));
			return;
		}	
		
		jQuery(this).parent().toggleClass("cancel")
			
		if(!xcloner_restore.get_cancel())
		{
			document.dispatchEvent(new CustomEvent("xcloner_restore_update_progress", {detail: {percent: 0, class: 'indeterminate' }}));
			xcloner_restore.remote_restore_backup_file(this.backup_file, this.remote_path);
		}
			
	})
	
})
