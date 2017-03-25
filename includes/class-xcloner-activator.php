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

	const xcloner_db_version = '1.1.7';
	const xcloner_minimum_version = '5.4.0';
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	
		global $wpdb;
		
		if(version_compare(phpversion(), Xcloner_Activator::xcloner_minimum_version, '<'))
		{
			wp_die('<p>'.sprintf(__("XCloner requires minimum PHP version %s in order to run correctly. We have detected your version as %s"),Xcloner_Activator::xcloner_minimum_version, phpversion()).'</p>',  __("XCloner Activation Error"), array( 'response'=>500, 'back_link'=>TRUE ) );
		}
		
		$charset_collate = $wpdb->get_charset_collate();	
		
		$installed_ver = get_option( "xcloner_db_version" );
		
		$xcloner_db_version = Xcloner_Activator::xcloner_db_version;
		
		$xcloner_scheduler_table = $wpdb->prefix . "xcloner_scheduler";
		
		if($installed_ver != $xcloner_db_version)		
		{
			$xcloner_schedule_sql="CREATE TABLE `".$xcloner_scheduler_table."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `recurrence` varchar(25) NOT NULL,
				  `params` text NOT NULL,
				  `start_at` datetime,
				  `remote_storage` varchar(10) DEFAULT NULL,
				  `hash` varchar(10) DEFAULT NULL,
				  `status` int(1) NOT NULL,
				  `last_backup` varchar(100) DEFAULT NULL,
				  PRIMARY KEY  (`id`)
				) ".$charset_collate.";
				";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $xcloner_schedule_sql );
			
			update_option( "xcloner_db_version", $xcloner_db_version );
		}
		
		if(get_option('xcloner_backup_compression_level') === false)
			update_option('xcloner_backup_compression_level', 0);
		
		if(get_option('xcloner_enable_log') === false)
			update_option('xcloner_enable_log', 1);
			
		if(get_option('xcloner_enable_mysql_backup') === false)
			update_option('xcloner_enable_mysql_backup', 1);
		
		if(get_option('xcloner_system_settings_page') === false)
			update_option('xcloner_system_settings_page', 100);
			
		if(get_option('xcloner_files_to_process_per_request') === false)
			update_option('xcloner_files_to_process_per_request', 250);

		if(get_option('xcloner_database_records_per_request') === false)
			update_option('xcloner_database_records_per_request', 10000);

		if(get_option('xcloner_exclude_files_larger_than_mb') === false)
			update_option('xcloner_exclude_files_larger_than_mb', 0);
		
		if(get_option('xcloner_split_backup_limit') === false)
			update_option('xcloner_split_backup_limit', 2048);
			
		if(get_option('xcloner_size_limit_per_request') === false)
			update_option('xcloner_size_limit_per_request', 50);
			
		if(get_option('xcloner_cleanup_retention_limit_days') === false)
			update_option('xcloner_cleanup_retention_limit_days', 60);
			
		if(get_option('xcloner_cleanup_retention_limit_archives') === false)
			update_option('xcloner_cleanup_retention_limit_archives', 100);
			
		if(get_option('xcloner_directories_to_scan_per_request') === false)
			update_option('xcloner_directories_to_scan_per_request', 25);
			
		/*if(!get_option('xcloner_diff_backup_recreate_period'))
			update_option('xcloner_diff_backup_recreate_period', 10);
			* */
			
		if(!get_option('xcloner_regex_exclude'))
			update_option('xcloner_regex_exclude', "(wp-content\/updraft|wp-content\/uploads\/wp_all_backup)(.*)$".PHP_EOL."(.*)\.(svn|git)(.*)$".PHP_EOL."wp-content\/cache(.*)$".PHP_EOL."(.*)error_log$");

	}
	
}
