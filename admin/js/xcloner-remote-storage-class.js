class Xcloner_Remote_Storage
{
	
	constructor()
	{
		
	}
	
	/** global: ajaxurl */
	toggle_status(elem)
	{
		var field = jQuery(elem).attr("name")
		var value = 0
		if(jQuery(elem).is(":checked"))
		{
			value = 1;
		}
			
		if(field){
			jQuery.ajax({
			  url: ajaxurl,
			  method: 'post',
			  data: { action : 'remote_storage_save_status', id: field, value: value},
			  success: function(response){
				  if(!response.finished)
					{
						alert('Error changing status')
					}
				  },
			  dataType: 'json'
			  
			})
		}
				
	}
}
