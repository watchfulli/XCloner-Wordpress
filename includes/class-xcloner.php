<?php
/**
 * XCloner - Backup and Restore backup plugin for Wordpress
 *
 * class-xcloner.php
 * @author Liuta Ovidiu <info@thinkovi.com>
 *
 *        This program is free software; you can redistribute it and/or modify
 *        it under the terms of the GNU General Public License as published by
 *        the Free Software Foundation; either version 2 of the License, or
 *        (at your option) any later version.
 *
 *        This program is distributed in the hope that it will be useful,
 *        but WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *        GNU General Public License for more details.
 *
 *        You should have received a copy of the GNU General Public License
 *        along with this program; if not, write to the Free Software
 *        Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *        MA 02110-1301, USA.
 *
 * @link https://github.com/ovidiul/XCloner-Wordpress
 *
 * @modified 7/31/18 3:29 PM
 *
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Xcloner
 * @subpackage Xcloner/includes
 * @author     Liuta Ovidiu <info@thinkovi.com>
 * @link       http://www.thinkovi.com
 */
class Xcloner
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Xcloner_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    protected $plugin_admin;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    private $xcloner_settings;
    private $xcloner_logger;
    private $xcloner_sanitization;
    private $xcloner_requirements;
    private $xcloner_filesystem;
    private $archive_system;
    private $xcloner_database;
    private $xcloner_scheduler;
    private $xcloner_remote_storage;
    private $xcloner_file_transfer;
    private $xcloner_encryption;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function init()
    {
        $this->log_php_errors();

        $this->plugin_name = 'xcloner';
        $this->version = '4.0.4';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

        $this->define_admin_menu();
        $this->define_plugin_settings();

        $this->define_ajax_hooks();
        $this->define_cron_hooks();
    }

    public function log_php_errors(){
        register_shutdown_function(array($this, 'exception_handler'));
    }

    /**
     * Dynamic get of class methods get_
     * @param $property
     * @param $args
     * @return mixed
     */
    public function __call($property, $args)
    {
        $property = str_replace("get_", "", $property);

        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    /**
     * Generate a random string of indicated length $length
     *
     * @param int $length
     * @return string
     */
    public function randomString($length = 6)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    public function check_dependencies()
    {
        $backup_storage_path =  (get_option('xcloner_store_path'));

        if (!$backup_storage_path) {
            $backup_storage_path = realpath(__DIR__ . DS . ".." . DS . ".." . DS . "..") . DS . "backups-" . $this->randomString('5') . DS;
        }

        if (!is_dir($backup_storage_path)) {
            if (!@mkdir($backup_storage_path)) {
                $status = "error";
                $message = sprintf(
                        __("Unable to create the Backup Storage Location Folder %s . Please fix this before starting the backup process."),
                        $backup_storage_path
                    );
                $this->trigger_message($message, $status, $backup_storage_path);
                return;
            }
        }
            
        if (!is_writable($backup_storage_path)) {
            $status = "error";
            $message = sprintf(
                    __("Unable to write to the Backup Storage Location Folder %s . Please fix this before starting the backup process."),
                    $backup_storage_path
                );
            $this->trigger_message($message, $status, $backup_storage_path);

            return;
        }

        update_option("xcloner_store_path", $backup_storage_path);
    }

    public function trigger_message($message, $status = "error", $message_param1 = "", $message_param2 = "", $message_param3 = "")
    {        
        $message = sprintf(__($message), $message_param1, $message_param2, $message_param3);
        add_action('xcloner_admin_notices', array($this, "trigger_message_notice"), 10, 2);
        do_action('xcloner_admin_notices', $message, $status);

        if (defined(XCLONER_STANDALONE_MODE) && XCLONER_STANDALONE_MODE) {
            throw new Error($message);
        }
    }

    public function trigger_message_notice($message, $status = "success")
    {
        ?>
		<div class="notice notice-<?php echo $status?> is-dismissible">
	        <p><?php _e($message, 'xcloner-backup-and-restore'); ?></p>
	    </div>
		<?php
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Xcloner_Loader. Orchestrates the hooks of the plugin.
     * - Xcloner_i18n. Defines internationalization functionality.
     * - Xcloner_Admin. Defines all hooks for the admin area.
     * - Xcloner_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    public function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-xcloner-admin.php';

        /**
         * The class responsible for debugging XCloner.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-logger.php';

        /**
         * The class responsible for defining the admin settings area.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-settings.php';

        /**
         * The class responsible for defining the Remote Storage settings area.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-remote-storage.php';

        /**
         * The class responsible for implementing the database backup methods.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-database.php';

        /**
         * The class responsible for sanitization of users input.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-sanitization.php';

        /**
         * The class responsible for XCloner system requirements validation.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-requirements.php';

        /**
         * The class responsible for XCloner backup archive creation.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-archive.php';

        /**
         * The class responsible for XCloner API requests.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-api.php';

        /**
         * The class responsible for the XCloner File System methods.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-file-system.php';

        /**
         * The class responsible for the XCloner File Transfer methods.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-file-transfer.php';

        /**
         * The class responsible for the XCloner Scheduler methods.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-scheduler.php';

        /**
         * The class responsible for the XCloner Encryption methods.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xcloner-encryption.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'public/class-xcloner-public.php';

        $this->loader = new Xcloner_Loader($this);
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Xcloner_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Xcloner_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

        //wp_localize_script( 'ajax-script', 'my_ajax_object',
        //   array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Xcloner_Admin($this);
        $this->plugin_admin = $plugin_admin;

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action('backup_archive_finished', $this, 'do_action_after_backup_finished', 10, 2);
    }

    /**
     * Register the Admin Sidebar menu
     *
     * @access    private
     *
     */
    private function define_admin_menu()
    {
        $this->loader->add_action('admin_menu', $this, 'xcloner_backup_add_admin_menu');
    }

    public function define_plugin_settings()
    {
        /**
         * register wporg_settings_init to the admin_init action hook
         */
        $this->xcloner_settings = new XCloner_Settings($this);

        if (defined('DOING_CRON') || isset($_POST['hash'])) {
            if (defined('DOING_CRON') || $_POST['hash'] == "generate_hash") {
                $this->xcloner_settings->generate_new_hash();
            } else {
                $this->xcloner_settings->set_hash($_POST['hash']);
            }
        }

        if (defined('DOING_CRON') || !isset($_POST['hash'])) {
            $this->loader->add_action('shutdown', $this, 'do_shutdown');
        }

        $this->xcloner_sanitization 	= new Xcloner_Sanitization();
        $this->xcloner_requirements 	= new Xcloner_Requirements($this);

        $this->loader->add_action('admin_init', $this->xcloner_settings, 'settings_init');

        //adding links to the Manage Plugins Wordpress page for XCloner
        $this->loader->add_filter('plugin_action_links', $this, 'add_plugin_action_links', 10, 2);
    }

    /**
     * Shutdown actions
     *
     * @return void
     */
    public function do_shutdown()
    {
        $this->xcloner_filesystem = new Xcloner_File_System($this);
        $this->xcloner_filesystem->remove_tmp_filesystem();
    }

    /*
     * @method static $this get_xcloner_logger()
     * @method static $this get_xcloner_settings()
     * type = core|plugin|theme|translation
     */
    public function pre_auto_update($type, $item, $context)
    {
        if (!$type) {
            return false;
        }

        $exclude_files = array();
        $regex = "";
        $data = "";

        $this->get_xcloner_logger()->info(sprintf("Doing automatic backup before %s upgrade, pre_auto_update hook.", $type));

        $content_dir = str_replace(ABSPATH, "", WP_CONTENT_DIR);
        $plugins_dir 	= str_replace(ABSPATH, "", WP_PLUGIN_DIR);
        $langs_dir 		= $content_dir.DS."languages";
        $themes_dir 		= $content_dir.DS."themes";

        switch ($type) {
            case 'core':
                $exclude_files = array(
                                    "^(?!(wp-admin|wp-includes|(?!.*\/.*.php)))(.*)$",
                                );
                break;
            case 'plugin':

                $dir_array = explode(DS, $plugins_dir);

                foreach ($dir_array as $dir) {
                    $data .= "\/".$dir;
                    $regex .= $data."$|";
                }

                $regex .= "\/".implode("\/", $dir_array);

                $exclude_files = array(
                                    "^(?!(".$regex."))(.*)$",
                                );
                break;
            case 'theme':

                $dir_array = explode(DS, $themes_dir);

                foreach ($dir_array as $dir) {
                    $data .= "\/".$dir;
                    $regex .= $data."$|";
                }

                $regex .= "\/".implode("\/", $dir_array);

                $exclude_files = array(
                                    "^(?!(".$regex."))(.*)$",
                                );
                break;
            case 'translation':

                $dir_array = explode(DS, $langs_dir);

                foreach ($dir_array as $dir) {
                    $data .= "\/".$dir;
                    $regex .= $data."$|";
                }

                $regex .= "\/".implode("\/", $dir_array);

                $exclude_files = array(
                                    "^(?!(".$regex."))(.*)$",
                                );
                break;
        }

        $schedule = array();

        $schedule['id'] = 0;
        $schedule['name'] = "pre_auto_update";
        $schedule['recurrence'] = "single";
        $schedule['excluded_files'] = json_encode($exclude_files);
        $schedule['table_params'] = json_encode(array("#" => array($this->get_xcloner_settings()->get_db_database())));

        $schedule['backup_params'] = new stdClass();
        $schedule['backup_params']->email_notification = get_option('admin_email');
        $schedule['backup_params']->backup_name = "backup_pre_auto_update_".$type."_[domain]-[time]-sql";

        try {
            $this->xcloner_scheduler->xcloner_scheduler_callback(0, $schedule);
        } catch (Exception $e) {
            $this->get_xcloner_logger()->error($e->getMessage());
        }
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Xcloner_Public($this);

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    public function exception_handler()
    {
        $logger = new XCloner_Logger($this, "php_system");
        $error = error_get_last();

        if ($error['type'] and $error['type'] === E_ERROR and $logger) {
            $logger->error($this->friendly_error_type($error['type']).": ".var_export($error, true));
        } elseif ($error['type'] and $logger) {
            $logger->debug($this->friendly_error_type($error['type']).": ".var_export($error, true));
        }
    }

    public function friendly_error_type($type)
    {
        static $levels = null;
        if ($levels === null) {
            $levels = [];
            foreach (get_defined_constants() as $key=>$value) {
                if (strpos($key, 'E_') !== 0) {
                    continue;
                }
                $levels[$value] = $key; //substr($key,2);
            }
        }
        return (isset($levels[$type]) ? $levels[$type] : "Error #{$type}");
    }

    /**
     * @method get_xcloner_settings()
     * @throws Exception
     */
    public function define_ajax_hooks()
    {
        //adding the pre-update hook

        if (is_admin() || defined('DOING_CRON')) {
            $this->xcloner_logger = new XCloner_Logger($this, "xcloner_api");
            $this->xcloner_filesystem = new Xcloner_File_System($this);

            //$this->xcloner_filesystem->set_diff_timestamp_start (strtotime("-15 days"));

            $this->archive_system = new Xcloner_Archive($this);
            $this->xcloner_database = new Xcloner_Database($this);
            $this->xcloner_scheduler = new Xcloner_Scheduler($this);
            $this->xcloner_remote_storage = new Xcloner_Remote_Storage($this);
            $this->xcloner_file_transfer 	= new Xcloner_File_Transfer($this);
            $this->xcloner_encryption    	= new Xcloner_Encryption($this);

            $xcloner_api = new Xcloner_Api($this);

            
            $this->loader->add_action('wp_ajax_get_database_tables_action', $xcloner_api, 'get_database_tables_action');
            $this->loader->add_action('wp_ajax_get_file_system_action', $xcloner_api, 'get_file_system_action');
            $this->loader->add_action('wp_ajax_scan_filesystem', $xcloner_api, 'scan_filesystem');
            $this->loader->add_action('wp_ajax_backup_database', $xcloner_api, 'backup_database');
            $this->loader->add_action('wp_ajax_backup_files', $xcloner_api, 'backup_files');
            $this->loader->add_action('wp_ajax_save_schedule', $xcloner_api, 'save_schedule');
            $this->loader->add_action('wp_ajax_get_schedule_by_id', $xcloner_api, 'get_schedule_by_id');
            $this->loader->add_action('wp_ajax_get_scheduler_list', $xcloner_api, 'get_scheduler_list');
            $this->loader->add_action('wp_ajax_delete_schedule_by_id', $xcloner_api, 'delete_schedule_by_id');
            $this->loader->add_action('wp_ajax_delete_backup_by_name', $xcloner_api, 'delete_backup_by_name');
            $this->loader->add_action('wp_ajax_download_backup_by_name', $xcloner_api, 'download_backup_by_name');
            $this->loader->add_action('wp_ajax_remote_storage_save_status', $xcloner_api, 'remote_storage_save_status');
            $this->loader->add_action('wp_ajax_upload_backup_to_remote', $xcloner_api, 'upload_backup_to_remote');
            $this->loader->add_action('wp_ajax_list_backup_files', $xcloner_api, 'list_backup_files');
            $this->loader->add_action('wp_ajax_restore_upload_backup', $xcloner_api, 'restore_upload_backup');
            $this->loader->add_action('wp_ajax_download_restore_script', $xcloner_api, 'download_restore_script');
            $this->loader->add_action('wp_ajax_copy_backup_remote_to_local', $xcloner_api, 'copy_backup_remote_to_local');
            $this->loader->add_action('wp_ajax_restore_backup', $xcloner_api, 'restore_backup');
            $this->loader->add_action('wp_ajax_backup_encryption', $xcloner_api, 'backup_encryption');
            $this->loader->add_action('wp_ajax_backup_decryption', $xcloner_api, 'backup_decryption');
            $this->loader->add_action('wp_ajax_get_manage_backups_list', $xcloner_api, 'get_manage_backups_list');
            $this->loader->add_action('admin_notices', $this, 'xcloner_error_admin_notices');
        }

        //Do a pre-update backup of targeted files
        if ($this->get_xcloner_settings()->get_xcloner_option('xcloner_enable_pre_update_backup')) {
            $this->loader->add_action("pre_auto_update", $this, "pre_auto_update", 1, 3);
        }
    }

    public function add_plugin_action_links($links, $file)
    {
        if ($file == plugin_basename(dirname(dirname(__FILE__)).'/xcloner.php')) {
            $links[] = '<a href="admin.php?page=xcloner_settings_page">'.__('Settings', 'xcloner-backup-and-restore').'</a>';
            $links[] = '<a href="admin.php?page=xcloner_generate_backups_page">'.__('Generate Backup', 'xcloner-backup-and-restore').'</a>';
        }

        return $links;
    }

    public function xcloner_error_admin_notices()
    {
        settings_errors('xcloner_error_message');
    }

    /**
     * @method get_xcloner_scheduler()
     */
    public function define_cron_hooks()
    {
        //registering new schedule intervals
        add_filter('cron_schedules', array($this, 'add_new_intervals'));


        $xcloner_scheduler = $this->get_xcloner_scheduler();
        $xcloner_scheduler->update_wp_cron_hooks();
    }

    /**
     * @param $schedules
     * @return mixed
     */
    public function add_new_intervals($schedules)
    {
        //weekly scheduler interval
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __('Once Weekly', 'xcloner-backup-and-restore')
        );

        //monthly scheduler interval
        $schedules['monthly'] = array(
            'interval' => 2635200,
            'display' => __('Once Monthly', 'xcloner-backup-and-restore')
        );

        //monthly scheduler interval
        $schedules['twicedaily'] = array(
            'interval' => 43200,
            'display' => __('Twice Daily', 'xcloner-backup-and-restore')
        );

        return $schedules;
    }

    /**
     * Add XCloner to Admin Menu
     */
    public function xcloner_backup_add_admin_menu()
    {
        if (function_exists('add_menu_page')) {
            add_menu_page(
                __('Site Backup', 'xcloner-backup-and-restore'),
                __('Site Backup', 'xcloner-backup-and-restore'),
                'manage_options',
                'xcloner_init_page',
                array($this, 'xcloner_display'),
                'dashicons-backup'
            );
        }

        if (function_exists('add_submenu_page')) {
            add_submenu_page(
                'xcloner_init_page',
                __('XCloner Dashboard', 'xcloner-backup-and-restore'),
                __('Dashboard', 'xcloner-backup-and-restore'),
                'manage_options',
                'xcloner_init_page',
                array($this, 'xcloner_display')
            );
            add_submenu_page(
                'xcloner_init_page',
                __('XCloner Backup Settings', 'xcloner-backup-and-restore'),
                __('Settings', 'xcloner-backup-and-restore'),
                'manage_options',
                'xcloner_settings_page',
                array($this, 'xcloner_display')
            );
            add_submenu_page(
                'xcloner_init_page',
                __('Remote Storage Settings', 'xcloner-backup-and-restore'),
                __('Remote Storage', 'xcloner-backup-and-restore'),
                'manage_options',
                'xcloner_remote_storage_page',
                array($this, 'xcloner_display')
            );
            add_submenu_page(
                'xcloner_init_page',
                __('Manage Backups', 'xcloner-backup-and-restore'),
                __('Manage Backups', 'xcloner-backup-and-restore'),
                'manage_options',
                'xcloner_manage_backups_page',
                array($this, 'xcloner_display')
            );
            add_submenu_page(
                'xcloner_init_page',
                __('Scheduled Backups', 'xcloner-backup-and-restore'),
                __('Scheduled Backups', 'xcloner-backup-and-restore'),
                'manage_options',
                'xcloner_scheduled_backups_page',
                array($this, 'xcloner_display')
            );
            add_submenu_page(
                'xcloner_init_page',
                __('Generate Backups', 'xcloner-backup-and-restore'),
                __('Generate Backups', 'xcloner-backup-and-restore'),
                'manage_options',
                'xcloner_generate_backups_page',
                array($this, 'xcloner_display')
            );
            add_submenu_page(
                'xcloner_init_page',
                __('Restore Backups', 'xcloner-backup-and-restore'),
                __('Restore Backups', 'xcloner-backup-and-restore'),
                'manage_options',
                'xcloner_restore_page',
                array($this, 'xcloner_display')
            );
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Xcloner_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    public function xcloner_display()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        $page = sanitize_key($_GET['page']);

        if ($page) {
            $this->display($page);
        }
    }

    public function display($page)
    {
        $plugin_admin = new Xcloner_Admin($this);
        $this->plugin_admin = $plugin_admin;

        call_user_func_array(array($this->plugin_admin, $page), array());
    }
}
