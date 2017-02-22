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

	const xcloner_db_version = '1.1';
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();	
		
		$installed_ver = get_option( "xcloner_db_version" );
		
		$xcloner_db_version = Xcloner_Activator::xcloner_db_version;
		
		if($installed_ver != $xcloner_db_version)
		{
			$table_name = $wpdb->prefix . "xcloner_scheduler";
		 
			$xcloner_schedule_sql="CREATE TABLE IF NOT EXISTS `".$table_name."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
				  `recurrence` varchar(10) CHARACTER SET latin1 NOT NULL,
				  `params` text CHARACTER SET latin1 NOT NULL,
				  `start_at` datetime,
				  `remote_storage` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `hash` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `status` int(1) NOT NULL,
				  `last_backup` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
				  PRIMARY KEY (`id`)
				) ".$charset_collate.";
				";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $xcloner_schedule_sql );
			
			update_option( "xcloner_db_version", $xcloner_db_version );
		}
	
		if(!get_option('xcloner_backup_compression_level'))
			update_option('xcloner_backup_compression_level', 0);
		
		if(!get_option('xcloner_enable_mysql_backup'))
			update_option('xcloner_enable_mysql_backup', 1);
		
		if(!get_option('xcloner_system_settings_page'))
			update_option('xcloner_system_settings_page', 100);
			
		if(!get_option('xcloner_files_to_process_per_request'))
			update_option('xcloner_files_to_process_per_request', 250);

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
			
		if(!get_option('xcloner_regex_exclude'))
			update_option('xcloner_regex_exclude', "(wp-content\/updraft|wp-content\/uploads\/wp_all_backup)(.*)$".PHP_EOL."(.*)\.(svn|git)(.*)$".PHP_EOL."wp-content\/cache(.*)$");

	}
	
}
