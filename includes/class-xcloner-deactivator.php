<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.thinkovi.com
 * @since      1.0.0
 *
 * @package    Xcloner
 * @subpackage Xcloner/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Xcloner
 * @subpackage Xcloner/includes
 * @author     Liuta Ovidiu <info@thinkovi.com>
 */
class Xcloner_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
		global $xcloner_plugin;
		
		if(is_a($xcloner_plugin, 'Xcloner'))
		{
			try{
				$xcloner_plugin->get_xcloner_filesystem()->cleanup_tmp_directories();
			}catch(Exception $e)
			{
				$xcloner_plugin->trigger_message_notice($e->getMessage());
			}
			
			$xcloner_scheduler = $xcloner_plugin->get_xcloner_scheduler();
			$xcloner_scheduler->deactivate_wp_cron_hooks();
		}
	}

}
