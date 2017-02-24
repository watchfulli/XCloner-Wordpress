<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.thinkovi.com
 * @since      1.0.0
 *
 * @package    Xcloner
 * @subpackage Xcloner/includes
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
 */
class Xcloner {

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
		register_shutdown_function(array($this, 'exception_handler'));
		
		$this->plugin_name = 'xcloner';
		$this->version = '1.0.0';
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		$this->define_admin_menu();
		$this->define_plugin_settings();
		
		$this->define_ajax_hooks();
		$this->define_cron_hooks();
		
	}
	
	public function check_dependencies(){
		
		$backup_storage_path = realpath(__DIR__.DS."..".DS."..".DS."..").DS."backups".DS;
		
		define("XCLONER_STORAGE_PATH", realpath($backup_storage_path));

		if(!is_dir($backup_storage_path))
		{
			if(!@mkdir($backup_storage_path))
			{
				$status = "error";
				$message = sprintf(__("Unable to create the Backup Storage Location Folder %s . Please fix this before starting the backup process."), $backup_storage_path);
				$this->trigger_message($message, $status, $backup_storage_path);
				return;
				//add_action( 'xcloner_admin_notices', array("Xcloner_Admin","trigger_message_notice"), 10, 2);
				//do_action( 'xcloner_admin_notices', $message, $status);
			}
		}	
		if(!is_writable($backup_storage_path))
		{
			$status = "error";
			$message = sprintf(__("Unable to write to the Backup Storage Location Folder %s . Please fix this before starting the backup process."), $backup_storage_path);
			$this->trigger_message($message, $status, $backup_storage_path);
			
			return;
		}
		
	}
	
	public function trigger_message($message, $status = "error", $message_param1 = "", $message_param2 = "", $message_param3 = "")
	{
			$message = sprintf(__($message), $message_param1, $message_param2, $message_param3);
			add_action( 'xcloner_admin_notices', array($this,"trigger_message_notice"), 10, 2);
			do_action( 'xcloner_admin_notices', $message, $status);
	}
	
	public function trigger_message_notice($message, $status = "success")
	{
		?>
		<div class="notice notice-<?php echo $status?> is-dismissible">
	        <p><?php _e( $message, 'xcloner' ); ?></p>
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
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-xcloner-admin.php';
		
		/**
		 * The class responsible for debugging XCloner.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-logger.php';
		
		/**
		 * The class responsible for defining the admin settings area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-settings.php';
		
		/**
		 * The class responsible for defining the Remote Storage settings area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-remote-storage.php';
		
		/**
		 * The class responsible for implementing the database backup methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-database.php';
		
		/**
		 * The class responsible for sanitization of users input.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-sanitization.php';
		
		/**
		 * The class responsible for XCloner system requirements validation.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-requirements.php';
		
		/**
		 * The class responsible for XCloner backup archive creation.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-archive.php';
		
		/**
		 * The class responsible for XCloner API requests.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-api.php';
		
		/**
		 * The class responsible for the XCloner File System methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-file-system.php';
		
		/**
		 * The class responsible for the XCloner File Transfer methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-file-transfer.php';
		
		/**
		 * The class responsible for the XCloner Scheduler methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xcloner-scheduler.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-xcloner-public.php';
		
		$this->loader = new Xcloner_Loader();

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
	private function set_locale() {

		$plugin_i18n = new Xcloner_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		
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
	private function define_admin_hooks() {
	
		$plugin_admin = new Xcloner_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->plugin_admin = $plugin_admin;

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
	}
	
	/**
	 * Register the Admin Sidebar menu
	 * 
	 * @access 	private
	 */
	private function define_admin_menu(){
		
		add_action('admin_menu', array($this->loader, 'xcloner_backup_add_admin_menu'));
		
	}
	
	private function define_plugin_settings(){
		/**
		* register wporg_settings_init to the admin_init action hook
		*/
		$settings = new Xcloner_Settings();
		add_action('admin_init', array($settings, 'settings_init'));
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Xcloner_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}
	
	public function exception_handler() {
		
		$logger = new XCloner_Logger("php_system");
		$error = error_get_last();
		
		if($error['type'] and $logger)
		{
			$logger->info($this->friendly_error_type ($error['type']).": ".var_export($error, true));
		}
	
	}
	
	function friendly_error_type($type) {
	    static $levels=null;
	    if ($levels===null) {
	        $levels=[];
	        foreach (get_defined_constants() as $key=>$value) {
	            if (strpos($key,'E_')!==0) {continue;}
					$levels[$value]= $key; //substr($key,2);
	        }
	    }
	    return (isset($levels[$type]) ? $levels[$type] : "Error #{$type}");
	}
		
	private function define_ajax_hooks()
	{
		$plugin_public = new Xcloner_Public( $this->get_plugin_name(), $this->get_version() );
		//$this->loader->add_action( 'wp_ajax_get_database_tables_action', $plugin_public, array('Xcloner_Api','get_database_tables_action') );
		
		if(is_admin())
		{
			$xcloner_api = new Xcloner_Api();

			add_action( 'wp_ajax_get_database_tables_action'	, array($xcloner_api,'get_database_tables_action')  );
			add_action( 'wp_ajax_get_file_system_action'		, array($xcloner_api,'get_file_system_action')  );
			add_action( 'wp_ajax_scan_filesystem'		, array($xcloner_api,'scan_filesystem')  );
			add_action( 'wp_ajax_backup_database'		, array($xcloner_api,'backup_database')  );
			add_action( 'wp_ajax_backup_files'		, array($xcloner_api,'backup_files')  );
			add_action( 'wp_ajax_save_schedule'		, array($xcloner_api,'save_schedule')  );
			add_action( 'wp_ajax_get_schedule_by_id'		, array($xcloner_api,'get_schedule_by_id')  );
			add_action( 'wp_ajax_get_scheduler_list'		, array($xcloner_api,'get_scheduler_list')  );
			add_action( 'wp_ajax_delete_schedule_by_id'		, array($xcloner_api,'delete_schedule_by_id')  );
			add_action( 'wp_ajax_delete_backup_by_name'		, array($xcloner_api,'delete_backup_by_name')  );
			add_action( 'wp_ajax_download_backup_by_name'		, array($xcloner_api,'download_backup_by_name')  );
			add_action( 'wp_ajax_remote_storage_save_status'		, array($xcloner_api,'remote_storage_save_status')  );
			add_action( 'wp_ajax_upload_backup_to_remote'		, array($xcloner_api,'upload_backup_to_remote')  );
			add_action( 'wp_ajax_list_backup_files'		, array($xcloner_api,'list_backup_files')  );
			add_action( 'wp_ajax_restore_upload_backup'		, array($xcloner_api,'restore_upload_backup')  );
			add_action( 'wp_ajax_download_restore_script'		, array($xcloner_api,'download_restore_script')  );
			add_action( 'admin_notices', array($this, 'xcloner_error_admin_notices' ));
		
		//if (is_admin()) {
            add_filter('plugin_action_links', array($this, 'add_plugin_action_links'), 10, 2);
        }
		
	}
	
	function add_plugin_action_links($links, $file) {
        if ($file == plugin_basename(dirname(dirname(__FILE__)) . '/xcloner.php'))
		{	
			$links[] = '<a href="admin.php?page=xcloner_settings_page">'.__('Settings', 'xcloner').'</a>';
			$links[] = '<a href="admin.php?page=xcloner_generate_backups_page">'.__('Generate Backup', 'xcloner').'</a>';
		}
        
        return $links;
    }
	
	public function xcloner_error_admin_notices() {
			settings_errors( 'xcloner_error_message' );
		}
	
	public function define_cron_hooks()
	{
		//registering new schedule intervals
		add_filter( 'cron_schedules', array($this, 'add_new_intervals'));
			
		
		$xcloner_scheduler = new Xcloner_Scheduler();
		$xcloner_scheduler->update_wp_cron_hooks();
		
	}
	
	public function xcloner_scheduler_callback($schedule_id)
	{
		$cron = new Xcloner_Scheduler;
		
		$cron->run_schedule($schedule_id);
	}
	
	function add_new_intervals($schedules) 
	{
		// add weekly and monthly intervals
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display' => __('Once Weekly')
		);
	
		$schedules['monthly'] = array(
			'interval' => 2635200,
			'display' => __('Once a month')
		);
	
		return $schedules;
	}

	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Xcloner_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	public function display($page)
	{
		$plugin_admin = new Xcloner_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->plugin_admin = $plugin_admin;
		
		$view = call_user_func_array(array($this->plugin_admin, $page), array());
	}
}
