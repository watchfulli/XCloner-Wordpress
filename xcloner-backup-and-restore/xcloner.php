<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://watchful.net
 * @since             1.0.0
 * @package           Xcloner
 *
 * @wordpress-plugin
 * Plugin Name: XCloner - Site Backup and Restore
 * Plugin URI: https://xcloner.com/
 * Description:  XCloner is a tool that will help you manage your website backups, generate/restore/move so your website will be always secured! With XCloner you will be able to clone your site to any other location with just a few clicks, as well as transfer the backup archives to remote FTP, SFTP, DropBox, Amazon S3, Google Drive, WebDAV, Backblaze, Azure accounts.
 * Version: 4.7.7
 * Author: watchful
 * Author URI: https://watchful.net/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: xcloner-backup-and-restore
 * Domain Path: /languages
 */
require_once(__DIR__ . '/vendor/autoload.php');

use Watchfulli\XClonerCore\Xcloner_Activator;
use Watchfulli\XClonerCore\Xcloner_cli;
use Watchfulli\XClonerCore\Xcloner_Deactivator;
use Watchfulli\XClonerCore\Xcloner;

if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}

if (!defined("XCLONER_PLUGIN_DIR")) {
    define("XCLONER_PLUGIN_DIR", dirname(__FILE__));
}

$xcloner_cli = new Xcloner_cli($argv ?? []);
if ($xcloner_cli->should_run()) {
    try {
        $xcloner_cli->run();
    } catch (Exception $e) {
      echo $e->getMessage() . "\n";
    }
    return;
}

// If this file is called directly, and we're not in CLI mode, then exit.
if (!defined('WPINC')) {
    die;
}


if (function_exists('register_activation_hook')) {
    register_activation_hook(__FILE__, [new Xcloner_Activator(), 'activate']);
}

if (function_exists('register_deactivation_hook')) {
    register_deactivation_hook(__FILE__, [new Xcloner_Deactivator(), 'deactivate']);
}

if (version_compare(phpversion(), Xcloner_Activator::xcloner_minimum_version, '<')) {
    ?>
    <div class="error notice">
        <p><?php echo sprintf(__("XCloner requires minimum PHP version %s in order to run correctly. We have detected your version as %s. Plugin is now deactivated."), Xcloner_Activator::xcloner_minimum_version, phpversion()) ?></p>
    </div>
    <?php
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

    if (function_exists('deactivate_plugins')) {
        deactivate_plugins(plugin_basename(__FILE__));
    }


    return;
}


// Don't load the plugin outside admin or cron
if (!is_admin() && !defined('DOING_CRON')) {
    //Check if we are running tests before leaving
    if (!defined('XCLONER_TESTING')) {
        return;
    }
}


$db_installed_ver = get_option("xcloner_db_version");
$xcloner_db_version = Xcloner_Activator::xcloner_db_version;

if ($db_installed_ver != $xcloner_db_version) {
    Xcloner_Activator::activate();
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
function xcloner_stop_heartbeat()
{
    wp_deregister_script('heartbeat');
}

if (isset($_GET['page']) && stristr($_GET['page'], "xcloner_")) {
    add_action('init', 'xcloner_stop_heartbeat', 1);
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @throws Exception
 * @since    1.0.0
 */
function run_xcloner()
{
    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path((__FILE__)) . 'admin/class-xcloner-admin.php';

    $xcloner = new Xcloner();
    $xcloner->check_dependencies();

    $xcloner->init();
    $xcloner->run();
}

try {
    run_xcloner();
} catch (Exception $e) {
    echo $e->getMessage();
}
