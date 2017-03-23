<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.thinkovi.com
 * @since      1.0.0
 *
 * @package    Xcloner
 * @subpackage Xcloner/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Xcloner
 * @subpackage Xcloner/admin
 * @author     Liuta Ovidiu <info@thinkovi.com>
 */
class Xcloner_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	private $xcloner_container;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( Xcloner $xcloner_container) {

		$this->plugin_name 			= $xcloner_container->get_plugin_name();
		$this->version				= $xcloner_container->get_version();
		$this->xcloner_container 	= $xcloner_container;
	}
	
	public function get_xcloner_container()
	{
		return $this->xcloner_container;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {
		
		if(!stristr($hook, "page_".$this->plugin_name) || (isset($_GET['option']) and $_GET['option']=="com_cloner"))
			return;

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Xcloner_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xcloner_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name."_materialize", plugin_dir_url( __FILE__ ) . 'css/materialize.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name."_materialize.clockpicker", plugin_dir_url( __FILE__ ) . 'css/materialize.clockpicker.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name."_materialize.icons", '//fonts.googleapis.com/icon?family=Material+Icons', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name."_jquery.datatables", plugin_dir_url( __FILE__ ) . 'css/jquery.dataTables.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name."_jquery.datatables.responsive", plugin_dir_url( __FILE__ ) . 'css/responsive.dataTables.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name."_jstree", dirname(plugin_dir_url( __FILE__ )) . '/vendor/vakata/jstree/dist/themes/default/style.min.css', array(), '3.3', 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xcloner-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

		if(!stristr($hook, "page_".$this->plugin_name))
			return;
			
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Xcloner_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xcloner_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		add_thickbox();
		wp_enqueue_script('plugin-install');
		wp_enqueue_script('updates');
		wp_enqueue_script( $this->plugin_name."_materialize", plugin_dir_url( __FILE__ ) . 'js/materialize.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."_materialize.clockpicker", plugin_dir_url( __FILE__ ) . 'js/materialize.clockpicker.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."_jquery.datatables", plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."_jquery.datatables.respnsive", plugin_dir_url( __FILE__ ) . 'js/dataTables.responsive.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."_vakata", dirname(plugin_dir_url( __FILE__ )) . '/vendor/vakata/jstree/dist/jstree.min.js', array( 'jquery' ), '3.3', false );
		wp_enqueue_script( $this->plugin_name."_xcloner-backup-class", plugin_dir_url( __FILE__ ) . 'js/xcloner-backup-class.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."_xcloner-scheduler-class", plugin_dir_url( __FILE__ ) . 'js/xcloner-scheduler-class.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."_xcloner-restore-class", plugin_dir_url( __FILE__ ) . 'js/xcloner-restore-class.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."_xcloner-manage-backups-class", plugin_dir_url( __FILE__ ) . 'js/xcloner-manage-backups-class.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."_xcloner-remote-storage-class", plugin_dir_url( __FILE__ ) . 'js/xcloner-remote-storage-class.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xcloner-admin.js', array( 'jquery' ), $this->version, false );
		

	}
	
	public function xcloner_init_page()
	{
		require_once("partials/xcloner_init_page.php");
		
	}
	
	public function xcloner_remote_storage_page()
	{
		$xcloner_sanitization = $this->get_xcloner_container()->get_xcloner_sanitization();
		$remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
		
		
		if(isset($_POST['action']))
		{
			$_POST['action'] = $xcloner_sanitization->sanitize_input_as_string($_POST['action']);
			$remote_storage->save($_POST['action']);
		}
		
		if(isset($_POST['authentification_code']) && $_POST['authentification_code'] != "")
		{
			$_POST['authentification_code'] = $xcloner_sanitization->sanitize_input_as_string($_POST['authentification_code']);
			
			$remote_storage->set_access_token($_POST['authentification_code']);
		}
			
		if(isset($_POST['connection_check']) && $_POST['connection_check'])
		{
			$remote_storage->check($_POST['action']);
		}
		
		require_once("partials/xcloner_remote_storage_page.php");
		
	}
	
	public function xcloner_scheduled_backups_page()
	{
		$requirements = $this->xcloner_container->get_xcloner_requirements();
		
		if(!$requirements->check_backup_ready_status())
		{
			require_once("partials/xcloner_init_page.php");
			return false;
		}
		
		require_once("partials/xcloner_scheduled_backups_page.php");
		
	}
	
	public function xcloner_manage_backups_page()
	{
		require_once("partials/xcloner_manage_backups_page.php");
		
	}
	
	public function xcloner_debugger_page()
	{
		require_once("partials/xcloner_console_page.php");
		
	}
	
	public function xcloner_restore_page()
	{
		require_once("partials/xcloner_restore_page.php");
		
	}
	
	public function xcloner_generate_backups_page()
	{
		$requirements = $this->xcloner_container->get_xcloner_requirements();
		
		if(!$requirements->check_backup_ready_status())
		{
			require_once("partials/xcloner_init_page.php");
			return false;
		}
		
		require_once("partials/xcloner_generate_backups_page.php");
		return;
	}
	
	public function xcloner_settings_page()
	{
		// check user capabilities
		if (!current_user_can('manage_options')) {
			return;
		}
		
		// add error/update messages
 
	    // check if the user have submitted the settings
	    // wordpress will add the "settings-updated" $_GET parameter to the url
	    if (isset($_GET['settings-updated'])) {
	        // add settings saved message with the class of "updated"
	        add_settings_error('wporg_messages', 'wporg_message', __('Settings Saved', 'wporg'), 'updated');
	    }
	 
	    // show error/update messages
	    settings_errors('wporg_messages');
	    ?>
	   
	    <?php
			$xcloner_sanitization = $this->get_xcloner_container()->get_xcloner_sanitization();
			
            if( isset( $_GET[ 'tab' ] ) ) {
                $active_tab = $xcloner_sanitization->sanitize_input_as_string($_GET[ 'tab' ]);
            } // end if
            else{
				$active_tab = "general_options";
			}
            
        ?>
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
         
        <ul class="nav-tab-wrapper row">
            <li><a href="?page=xcloner_settings_page&tab=general_options" class="nav-tab col s12 m3 l2 <?php echo $active_tab == 'general_options' ? 'nav-tab-active' : ''; ?>"><?php echo __('General Options', 'xcloner-backup-and-restore')?></a></li>
            <li><a href="?page=xcloner_settings_page&tab=mysql_options" class="nav-tab col s12 m3 l2 <?php echo $active_tab == 'mysql_options' ? 'nav-tab-active' : ''; ?>"><?php echo __('Mysql Options', 'xcloner-backup-and-restore')?></a></li>
            <li><a href="?page=xcloner_settings_page&tab=system_options" class="nav-tab col s12 m3 l2 <?php echo $active_tab == 'system_options' ? 'nav-tab-active' : ''; ?>"><?php echo __('System Options', 'xcloner-backup-and-restore')?></a></li>
            <li><a href="?page=xcloner_settings_page&tab=cleanup_options" class="nav-tab col s12 m3 l2 <?php echo $active_tab == 'cleanup_options' ? 'nav-tab-active' : ''; ?>"><?php echo __('Cleanup Options', 'xcloner-backup-and-restore')?></a></li>
        </ul>

	    <div class="wrap">
	        
	        <form action="options.php" method="post">
	            <?php
				
				if( $active_tab == 'general_options' ) {
					
					settings_fields('xcloner_general_settings_group');
					do_settings_sections('xcloner_settings_page');
					
				}elseif( $active_tab == 'mysql_options' ) {
					
					settings_fields('xcloner_mysql_settings_group');
					do_settings_sections('xcloner_mysql_settings_page');
				}elseif( $active_tab == 'system_options' ) {
					
					settings_fields('xcloner_system_settings_group');
					do_settings_sections('xcloner_system_settings_page');
				}elseif( $active_tab == 'cleanup_options' ) {
					
					settings_fields('xcloner_cleanup_settings_group');
					do_settings_sections('xcloner_cleanup_settings_page');
				}

	            // output save settings button
	            submit_button('Save Settings');
	            ?>
	        </form>

	    </div>
	    <?php
		
	}

}
