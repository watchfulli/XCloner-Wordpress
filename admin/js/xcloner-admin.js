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
		
		jQuery(".btn.system_info_toggle").click(function(){
			jQuery(".additional_system_info").toggle();
		})
		
		jQuery(".nav-tab-wrapper.content li").click(function(){
				jQuery(".nav-tab-wrapper li a").removeClass("nav-tab-active");
				jQuery(this).find('a').addClass("nav-tab-active");
				jQuery(".nav-tab-wrapper-content .tab-content").removeClass('active');
				jQuery(".nav-tab-wrapper-content "+jQuery(this).find('a').attr('href')).addClass('active');
		})
	
		var hash = window.location.hash;
		if(hash){
			next_tab(hash);
		}
	})
	
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );

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
