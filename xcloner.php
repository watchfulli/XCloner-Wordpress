<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.thinkovi.com
 * @since             1.0.0
 * @package           Xcloner
 *
 * @wordpress-plugin
 * Plugin Name:       XCloner - Site Backup and Restore
 * Plugin URI:        http://www.xcloner.com
 * Description:       XCloner is a tool that will help you manage your website backups, generate/restore/move so your website will be always secured! With XCloner you will be able to clone your site to any other location with just a few clicks, as well as transfer the backup archives to remote FTP, SFTP accounts.
 * Version:           4.0.0
 * Author:            Liuta Ovidiu
 * Author URI:        http://www.thinkovi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xcloner
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define("DS", DIRECTORY_SEPARATOR);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xcloner-activator.php
 */
function activate_xcloner() 
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xcloner-activator.php';
	Xcloner_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-xcloner-deactivator.php
 */
function deactivate_xcloner() 
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xcloner-deactivator.php';
	Xcloner_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_xcloner' );
register_deactivation_hook( __FILE__, 'deactivate_xcloner' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
 	
require_once(plugin_dir_path( __FILE__ )  . '/vendor/autoload.php');
require plugin_dir_path( __FILE__ ) . 'includes/class-xcloner.php';


function xcloner_display()
{	
	// check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
	
	$page = sanitize_key($_GET['page']);
	$plugin = new Xcloner();
	if($page)
		$plugin->display($page);
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xcloner() 
{
	$plugin = new Xcloner();
	$plugin->check_dependencies();
	$plugin->init();
	$plugin->run();

}

run_xcloner();
