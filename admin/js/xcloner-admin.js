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
