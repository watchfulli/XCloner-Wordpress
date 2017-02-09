class Xcloner_Restore{

	constructor()
	{
		this.steps = ['restore-script-upload-step','backup-upload-step']
	}
	
	verify(response, status)
	{
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
	
	do_ajax(callback)
	{
		if(!this.restore_script_url)
			return false;
		
		var $this = this;
			
		jQuery.ajax({
			url: this.restore_script_url,
			dataType: 'json',
			type: 'POST',
			data: {},
			error: function(err) {
				$this[callback](err, false);
				}
			}).done(function(json) {
				if(json.status != 200){
						$this[callback](json, false);
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
	
}

jQuery(document).ready(function(){
	
	var xcloner_restore = new Xcloner_Restore();
	
	xcloner_restore.set_current_step(0);
	
	jQuery('select').material_select();
	
	jQuery(".xcloner-restore #validate_url").on("click", function(){
			
		xcloner_restore.set_restore_script_url(jQuery(".xcloner-restore #restore_script_url").val());
		xcloner_restore.verify();	
			
	})
	
})
