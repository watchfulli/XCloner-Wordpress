<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.thinkovi.com
 * @since      1.0.0
 *
 * @package    Xcloner
 * @subpackage Xcloner/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Xcloner
 * @subpackage Xcloner/includes
 * @author     Liuta Ovidiu <info@thinkovi.com>
 */
class Xcloner_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		if(!get_option('xcloner_backup_compression_level'))
			update_option('xcloner_backup_compression_level', 0);
		
		if(!get_option('xcloner_enable_mysql_backup'))
			update_option('xcloner_enable_mysql_backup', 1);
		
		if(!get_option('xcloner_system_settings_page'))
			update_option('xcloner_system_settings_page', 100);

		if(!get_option('xcloner_database_records_per_request'))
			update_option('xcloner_database_records_per_request', 10000);

		if(!get_option('xcloner_exclude_files_larger_than_mb'))
			update_option('xcloner_exclude_files_larger_than_mb', 0);
		
		if(!get_option('xcloner_split_backup_limit'))
			update_option('xcloner_split_backup_limit', 2048);
			
		if(!get_option('xcloner_size_limit_per_request'))
			update_option('xcloner_size_limit_per_request', 50);
			
		if(!get_option('xcloner_cleanup_retention_limit_days'))
			update_option('xcloner_cleanup_retention_limit_days', 60);
			
		if(!get_option('xcloner_cleanup_retention_limit_archives'))
			update_option('xcloner_cleanup_retention_limit_archives', 100);
			
		if(!get_option('xcloner_directories_to_scan_per_request'))
			update_option('xcloner_directories_to_scan_per_request', 25);

	}

}
