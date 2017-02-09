class Xcloner_Restore{

	constructor()
	{
		this.steps = ['restore-script-upload-step','backup-upload-step']
		this.ajaxurl = ajaxurl;
		this.cancel = false;
		
	}
	
	upload_backup_file(file)
	{
		this.ajaxurl = ajaxurl;
		var params = new Object()
		
		params.file = file;
		params.start = 0;
		params.target_url = this.restore_script_url
		
		jQuery(".xcloner-restore .backup-upload-step .progress .determinate").css("width", "0%")
		
		this.do_ajax('upload_backup_file_callback', 'restore_upload_backup', params)
	}
	
	
	upload_backup_file_callback(response, status, params = new Object())
	{
		if(response.start !== false)
		{
			var percent = 0;
			if(response.total_size)
				percent = (100*parseFloat(response.start))/parseFloat(response.total_size)
			jQuery(".xcloner-restore .backup-upload-step .progress .determinate").css("width", percent+"%")	
			params.start = response.start;
			this.do_ajax('upload_backup_file_callback', 'restore_upload_backup', params)
		}
		else
		{
			jQuery(".xcloner-restore .backup-upload-step .progress .determinate").css("width", "100%")
			this.cancel = false
			jQuery(".xcloner-restore #upload_backup").show();
			jQuery(".xcloner-restore #cancel_upload_backup").hide();
		}
	}
	
	verify(response, status, params = new Object())
	{
		this.ajaxurl = this.restore_script_url;
		
		if(!response)
			this.do_ajax("verify");
		else
		{
			if(!status)
			{
				jQuery(".xcloner-restore #restore_script_url").addClass("invalid").removeClass('valid');
				jQuery(".xcloner-restore #url_validation_status").text(response.status+" "+response.statusText);
				jQuery(".xcloner-restore #validate_url .material-icons").text("error");
				
			}else{
				jQuery(".xcloner-restore #validate_url .material-icons").text("check_circle");
				jQuery(".xcloner-restore #restore_script_url").removeClass("invalid").addClass('valid');
				jQuery(".xcloner-restore #url_validation_status").text("");
				this.next_step();
			}
		}	
	}
	
	next_step()
	{
		this.set_current_step++;
		jQuery(".xcloner-restore li."+this.steps[this.set_current_step]).addClass('active').show().find(".collapsible-header").trigger('click');
	}
	
	do_ajax(callback, action="", params= new Object())
	{
		params.action = action
		if(this.cancel == true)
			return;
		
		if(!this.restore_script_url)
			return false;
		
		var $this = this;
			
		jQuery.ajax({
			url: this.ajaxurl,
			dataType: 'json',
			type: 'POST',
			data: params,
			error: function(err) {
				$this[callback](err, false);
				}
			}).done(function(json) {
				console.log(json)
				if(json.status != 200){
						$this[callback](json, false, params);
						return;
				}
				$this[callback](json, true);
			});
	}
	
	show_ajax_error(title = "", status="", msg)
	{
		alert(status+" "+msg);
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
		this.cancel = status
	}
	
	get_cancel(status)
	{
		return this.cancel
	}
	
	
}

jQuery(document).ready(function(){
	
	var xcloner_restore = new Xcloner_Restore();
	
	xcloner_restore.set_current_step(0);
	
	jQuery('select').material_select();
	
	jQuery(".xcloner-restore #cancel_upload_backup").on("click", function(){
		jQuery(".xcloner-restore #upload_backup").show();
		jQuery(this).hide();
		xcloner_restore.set_cancel(true);
	})
	
	jQuery(".xcloner-restore #upload_backup").on("click",function(){
		
		xcloner_restore.set_cancel(false);
		
		var backup_file = jQuery(".xcloner-restore #backup_file").val();
		if(backup_file)
		{
			jQuery(".xcloner-restore #cancel_upload_backup").show();
			jQuery(this).hide();
			xcloner_restore.upload_backup_file(backup_file);
		}
	})
	
	jQuery(".xcloner-restore #validate_url").on("click", function(){
			
		xcloner_restore.set_restore_script_url(jQuery(".xcloner-restore #restore_script_url").val());
		xcloner_restore.verify();	
			
	})
	
})
