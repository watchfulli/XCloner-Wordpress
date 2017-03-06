(function( $ ) {
	'use strict';

	jQuery(document).ready(function(){
	
		jQuery("span.shorten_string").click(function(){
			jQuery(this).toggleClass("full");
			doShortText(jQuery(this));
			})
			
		jQuery("span.shorten_string").each(function(){
			doShortText(jQuery(this));
		})
		
		jQuery("#xcloner_regex_exclude").on("focus", function(){
			jQuery("ul.xcloner_regex_exclude_limit li").fadeIn();
			})
		
		jQuery(".regex_pattern").on("click", function(){
			jQuery(this).select();
		})
		
		jQuery(".btn.system_info_toggle").on("click", function(){
			jQuery(".additional_system_info").toggle();
		})
		
		jQuery("a.download-logger").on("click", function(e){
			var xcloner_manage_backups = new Xcloner_Manage_Backups();
			
			var hash = jQuery(this).attr('href');
			var id = hash.substr(1)
			
			if(id)
			{
				xcloner_manage_backups.download_backup_by_name(id);
			}
			
			e.preventDefault();
		})
		
		jQuery(".nav-tab-wrapper.content li").on("click", function(e){
				jQuery(".nav-tab-wrapper li a").removeClass("nav-tab-active");
				jQuery(this).find('a').addClass("nav-tab-active");
				jQuery(".nav-tab-wrapper-content .tab-content").removeClass('active');
				jQuery(".nav-tab-wrapper-content "+jQuery(this).find('a').attr('href')).addClass('active');
				
				e.preventDefault();
				
				location.hash = jQuery(this).find('a').attr('href')+"_hash";
				
		})
	
		var hash = window.location.hash;
		if(hash){
			next_tab(hash.replace("_hash",""));
		}
	})
	
})( jQuery );




//jQuery( document ).ajaxError(function(err, request) {
		//show_ajax_error("dd", "dd12", request)
//});

function next_tab(hash){
		jQuery(".nav-tab-wrapper").find("li a[href='"+hash+"']").trigger('click');
		location.hash = hash;
	}
	
function doShortText(elem)
{
	if(elem.hasClass("full")){
		elem.text(elem.attr("data-text"));
		return;
	}
	var text = elem.text()
	var text_lenght = text.length;
	var first = text.substr(0, 10);
	var last = text.substr(text_lenght-20, text_lenght);
	
	elem.attr("data-text", text).text(first+"..."+last);
}

/** global: xcloner_backup */
function show_ajax_error(title, msg, json){
	
	//var json = jQuery.parseJSON( body )
	
	if(typeof xcloner_backup !== 'undefined')
	{
		xcloner_backup.cancel_backup();
	}
	
	if(json.responseText)
	{
		msg = msg+": "+json.responseText;
	}
		
	jQuery("#error_modal .title").text(title);
	jQuery("#error_modal .msg").text(msg);
	
	if(json.status)
	{
		jQuery("#error_modal .status").text(json.status+" "+json.statusText);
	}
		
	jQuery("#error_modal .body").text(JSON.stringify(json));
	var error_modal = jQuery("#error_modal").modal();
	error_modal.modal('open');
}

var ID = function () {
  // Math.random should be unique because of its seeding algorithm.
  // Convert it to base 36 (numbers + letters), and grab the first 9 characters
  // after the decimal.
  return '_' + Math.random().toString(36).substr(2, 9);
};


var getUrlParam = function(name) {
    return (location.search.split(name + '=')[1] || '').split('&')[0];
}


