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
 * Version: 4.3.7
 * Author: watchful
 * Author URI: https://watchful.net/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: xcloner-backup-and-restore
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}

if (!defined("XCLONER_PLUGIN_DIR")) {
    define("XCLONER_PLUGIN_DIR", plugin_dir_path(__FILE__));
}

// composer library autoload
require_once(__DIR__ . '/vendor/autoload.php');

use Watchfulli\XClonerCore\Xcloner_Activator;
use Watchfulli\XClonerCore\Xcloner_Deactivator;
use Watchfulli\XClonerCore\Xcloner;


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
        $opts = getopt('v::p:h::q::e:d:k:l:', array('verbose::', 'profile:', 'help::', 'quiet::', 'encrypt:', 'decrypt:', 'key:', 'list:'));
    }

    if (!sizeof($opts)) {
        $opts['h'] = true;
    }

    if (isset($opts['h']) || isset($opts['help'])) {
        echo sprintf("-h                        Display help\n");
        echo sprintf("-p <profile name>         Specify the backup profile name or ID\n");
        echo sprintf("-e <backup name>          Encrypt backup file\n");
        echo sprintf("-d <backup name>          Decrypt backup file\n");
        echo sprintf("-k <encryption key>       Encryption/Decryption Key\n");
        echo sprintf("-l <backup name>          List files inside backup\n");
        echo sprintf("-v                        Verbose output\n");
        echo sprintf("-q                        Disable output\n");
        return;
    }

    if (isset($opts['q']) || isset($opts['quiet'])) {
        define('XCLONER_DISABLE_OUTPUT', true);
    }

    if (isset($opts['v']) || isset($opts['verbose'])) {
        define('WP_DEBUG', true);
        define('WP_DEBUG_DISPLAY', true);
    } else {
        define('WP_DEBUG', false);
        define('WP_DEBUG_DISPLAY', false);
    }

    if (file_exists(__DIR__ . "/../../../wp-load.php")) {
        require_once(__DIR__ . '/../../../wp-load.php');
    }

    $profile = [
        'id' => 0
    ];

    $profile_name = "";

    // --profile|p profile name
    if (isset($opts['p']) && $opts['p']) {
        $profile_name = $opts['p'];
    } elseif (isset($opts['profile']) && $opts['profile']) {
        $profile_name = $opts['profile'];
    }

    $xcloner_backup = new Xcloner();

    // --list|l list backup archive
    if (isset($opts['l']) || isset($opts['list'])) {
        $backup_file_path = $opts['list'] . $opts['l'];

        // function to list backup content recursively
        $list_backup_archive_contents = function ($backup_name, $start = 0) use ($xcloner_backup) {
            $xcloner_settings = $xcloner_backup->get_xcloner_settings();
            $xcloner_file_system = $xcloner_backup->get_xcloner_filesystem();

            if ($xcloner_backup->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
                die(sprintf("%s file is encrypted, please decrypt it first! \n", $backup_name));
            }

            $tar = $xcloner_backup->get_archive_system();

            $backup_parts = [$backup_name];

            if ($xcloner_file_system->is_multipart($backup_name)) {
                $backup_parts = $xcloner_file_system->get_multipart_files($backup_name);
            }

            foreach ($backup_parts as $backup_name) {
                if (!$start) {
                    echo sprintf("Processing %s \n", $backup_name);
                }
                $tar->open($xcloner_settings->get_xcloner_store_path() . DS . $backup_name, $start);

                $data = $tar->contents($xcloner_settings->get_xcloner_option('xcloner_files_to_process_per_request'));

                foreach ($data['extracted_files'] as $key => $file) {
                    echo sprintf("%s (%s) \n", $file->getPath(), size_format($file->getSize()));
                }

                if (isset($data['start'])) {
                    call_user_func(__FUNCTION__, $backup_name, $data['start']);
                }
            }
        };

        $list_backup_archive_contents($backup_file_path);

        exit;
    }

    // --key|k encryption key
    $encryption_key = "";
    if (isset($opts['k']) || isset($opts['key'])) {
        $encryption_key = $opts['key'] . $opts['k'];
    }

    // --encrypt|e encrypt backup archive
    if (isset($opts['e']) || isset($opts['encrypt'])) {
        $backup_name = $opts['encrypt'] . $opts['e'];
        if (!$xcloner_backup->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
            $xcloner_backup->get_xcloner_encryption()->encrypt_file($backup_name, "", $encryption_key, 0, 0, true, true);
        } else {
            die(sprintf('File %s is already encrypted\n', $backup_name));
        }
    }

    // --decrypt|d decrypt backup archive
    if (isset($opts['d']) || isset($opts['decrypt'])) {
        $backup_name = $opts['decrypt'] . $opts['d'];
        if ($xcloner_backup->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
            $xcloner_backup->get_xcloner_encryption()->decrypt_file($backup_name, "", $encryption_key, 0, 0, true);
        } else {
            die(sprintf('File %s is already decrypted\n', $backup_name));
        }
    }

    // start schedule based on profile name
    if (!empty($profile_name)) {
        $profile = ($xcloner_backup->get_xcloner_scheduler()->get_schedule_by_id_or_name($profile_name));

        if ($profile['id']) {
            $xcloner_backup->execute_backup($profile['id']);
        } else {
            die(sprintf('Could not find profile %s', $profile_name));
        }
    }
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
             * [--profile=<profile>]
             * : backup profile name or id
             *
             * [--encrypt=<backup_name>]
             * : encrypt backup archive
             *
             * [--decrypt=<backup_name>]
             * : decrypt backup archive
             *
             * [--key=<encryption_key>]
             * : custom encryption/decryption key
             *
             * [--list=<backup_name>]
             * : list backup archive contents
             *
             * @when before_wp_load
             */
            function ($args, $assoc_args) {
                if (WP_CLI::get_config('quiet')) {
                    $assoc_args['quiet'] = true;
                }
                do_cli_execution($args, $assoc_args);
            }
        );
    } elseif (isset($argv) && basename($argv[0]) == "xcloner.php") {
        do_cli_execution();
    }
}

//i will not load the plugin outside admin or cron
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
