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
 * Plugin Name:       XCloner - Site Backup and Restore
 * Plugin URI:        https://xcloner.com/
 * Description:       XCloner is a tool that will help you manage your website backups, generate/restore/move so your website will be always secured! With XCloner you will be able to clone your site to any other location with just a few clicks, as well as transfer the backup archives to remote FTP, SFTP, DropBox, Amazon S3, Google Drive, WebDAV, Backblaze, Azure accounts.
 * Version:           4.2.9
 * Author:            watchful
 * Author URI:        https://watchful.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xcloner-backup-and-restore
 * Domain Path:       /languages
 */

require_once __DIR__.'/includes/class-xcloner-activator.php';

if (function_exists('register_activation_hook')) {
    register_activation_hook(__FILE__, 'activate_xcloner');
}

if (function_exists('register_deactivation_hook')) {
    register_deactivation_hook(__FILE__, 'deactivate_xcloner');
}

if (version_compare(phpversion(), Xcloner_Activator::xcloner_minimum_version, '<')) {
    ?>
    <div class="error notice">
        <p><?php echo sprintf(__("XCloner requires minimum PHP version %s in order to run correctly. We have detected your version as %s. Plugin is now deactivated."), Xcloner_Activator::xcloner_minimum_version, phpversion()) ?></p>
    </div>
	<?php
    include_once(ABSPATH.'wp-admin/includes/plugin.php');

    if (function_exists('deactivate_plugins')) {
        deactivate_plugins(plugin_basename(__FILE__));
    }


    return;
}


// composer library autoload
require_once(__DIR__.'/vendor/autoload.php');

/**
 * Execute xcloner in CLI mode
 *
 * @param array $args
 * @param array $opts
 * @return void
 */
function do_cli_execution($args = array(), $opts = array())
{
    if (!sizeof($opts)) {
        $opts = getopt('v::p:h::', array('verbose::','profile:','help:'));
    }

    if (isset($opts['h']) || isset($opts['help'])) {
        echo sprintf("-h                Display help\n");
        echo sprintf("-p <profile name> Specify the backup profile name or ID\n");
        echo sprintf("-v                Verbose output\n");
        return;
    }

    if (isset($opts['v']) || isset($opts['verbose'])) {
        define('WP_DEBUG', true);
        define('WP_DEBUG_DISPLAY', true);
    } else {
        define('WP_DEBUG', false);
        define('WP_DEBUG_DISPLAY', false);
    }

    if (file_exists(__DIR__ . "/../../../wp-load.php")) {
        require_once(__DIR__ .'/../../../wp-load.php');
    }
  
    $profile = [
 'id' => 0
];

    $profile_name = "undefined";

    if (isset($opts['p']) && $opts['p']) {
        $profile_name = $opts['p'];
    } elseif (isset($opts['profile']) && $opts['profile']) {
        $profile_name = $opts['profile'];
    }

    //pass json config to Xcloner_Standalone lib
    $xcloner_backup = new watchfulli\XClonerCore\Xcloner_Standalone();

    if (isset($profile_name) && $profile_name) {
        $profile = ($xcloner_backup->get_xcloner_scheduler()->get_schedule_by_id_or_name($profile_name));
    }

    if ($profile['id']) {
        $xcloner_backup->start($profile['id']);
    } else {
        die("Could not find profile ". $profile_name."\n");
    }

    return;
}

$foo = function ($args, $assoc_args) {
    WP_CLI::success($args[0] . ' ' . $assoc_args['append']);
};


//detect CLI mode
if (php_sapi_name() == "cli") {
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::add_command(
            'xcloner_generate_backup',
    /**
     * XCloner Generate backup based on supplied profile Name or ID
     *
     * --profile=<profile>
     * : backup profile name or id
     *
     * @when before_wp_load
     */
    function ($args, $assoc_args) {
        return do_cli_execution($args, $assoc_args);
    }
        );
    } elseif (isset($argv) && basename($argv[0]) == "xcloner.php") {
        return do_cli_execution();
    }
}

  
    
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

//i will not load the plugin outside admin or cron
if (!is_admin() && !defined('DOING_CRON')) {
    return;
}

if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xcloner-activator.php
 */
function activate_xcloner()
{
    require_once plugin_dir_path(__FILE__).'includes/class-xcloner-activator.php';
    Xcloner_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-xcloner-deactivator.php
 */
function deactivate_xcloner()
{
    require_once plugin_dir_path(__FILE__).'includes/class-xcloner-deactivator.php';
    Xcloner_Deactivator::deactivate();
}

$db_installed_ver   = get_option("xcloner_db_version");
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

if (isset($_GET['page']) and stristr($_GET['page'], "xcloner_")) {
    add_action('init', 'xcloner_stop_heartbeat', 1);
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
    $plugin = new \Xcloner();
    $plugin->check_dependencies();

    /**
    * The class responsible for defining all actions that occur in the admin area.
    */
    require_once plugin_dir_path((__FILE__)).'admin/class-xcloner-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    //require_once plugin_dir_path((__FILE__)).'public/class-xcloner-public.php';
        
    $plugin->init();
    $plugin->extra_define_ajax_hooks();
    $plugin->run();

    

    return $plugin;
}

require plugin_dir_path(__FILE__).'includes/class-xcloner.php';

try {
    $xcloner_plugin = run_xcloner();
} catch (Exception $e) {
    echo $e->getMessage();
}
