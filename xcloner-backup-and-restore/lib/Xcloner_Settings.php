<?php
namespace Watchfulli\XClonerCore;

use Exception;

class Xcloner_Settings
{
    private $logger_file = "xcloner_main_%s.log";
    private $logger_file_hash = "xcloner%s.log";
    private $xcloner_options_prefix = "xcloner";

    private $hash;

    /** @var Xcloner_Sanitization */
    private $xcloner_sanitization;
    /** @var Xcloner  */
    private $xcloner_container;

    /**
     * XCloner General Settings Class
     *
     * @param Xcloner $xcloner_container
     */
    public function __construct(Xcloner $xcloner_container)
    {
        $this->xcloner_container = $xcloner_container;
        $this->xcloner_sanitization = $this->xcloner_container->get_xcloner_sanitization();
    }

    /**
     * Restore default XCloner settings by deleting all existing values
     */
    public function restore_defaults() {
        global $wpdb;

        return $wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'xcloner_%'" );
    }

    /**
     * XCloner options prefix
     *
     * @return string
     */
    public function get_options_prefix() {
        return $this->xcloner_options_prefix;
    }

    /**
     * Get Logger Filename Setting
     *
     * @param integer $include_hash
     */
    public function get_logger_filename($include_hash = 0)
    {
        if ($include_hash) {
            $filename = sprintf($this->logger_file_hash, $this->get_hash());
        } else {
            $filename = sprintf($this->logger_file, $this->get_server_unique_hash(5));
        }

        return $filename;
    }

    /**
     * Get XCloner Backup Start Path Setting
     *
     */
    public function get_xcloner_start_path()
    {
        if (!$this->get_xcloner_option('xcloner_start_path') || !is_dir(/** @scrutinizer ignore-type */$this->get_xcloner_option('xcloner_start_path'))) {
            $path = realpath(ABSPATH);
        } else {
            $path = $this->get_xcloner_option('xcloner_start_path');
        }

        return $path;
    }

    /**
     * Get XCloner Backup Store Path Setting
     *
     * @return string
     * @throws Exception
    */
    public function get_xcloner_store_path()
    {
        if (!$this->get_xcloner_option('xcloner_store_path') or !is_dir(/** @scrutinizer ignore-type */$this->get_xcloner_option('xcloner_store_path'))) {
          $this->xcloner_container->check_dependencies();
        }

        return $this->get_xcloner_option('xcloner_store_path');
    }

    /**
     * Get XCloner Encryption Key
     *
     */
    public function get_xcloner_encryption_key()
    {
        if (!$key = $this->get_xcloner_option('xcloner_encryption_key')) {
            $key = $this->xcloner_container->randomString(35);
            update_option('xcloner_encryption_key', $key);
        }

        return $key;
    }

    public function get_xcloner_tmp_path_suffix()
    {
        return "xcloner".$this->get_hash();
    }

    /**
     * Get XCloner Temporary Path
     *
     * @param boolean $suffix
     * @throws Exception
     */
    public function get_xcloner_tmp_path($suffix = true)
    {
        if ($this->get_xcloner_option('xcloner_force_tmp_path_site_root')) {
            $path = $this->get_xcloner_store_path();
        } else {
            $path = sys_get_temp_dir();
            if (!is_dir($path)) {
                try {
                    mkdir($path);
                } catch (Exception $e) {
                    //silent catch
                }
            }

            if (!is_dir($path) || !is_writeable($path)) {
                $path = $this->get_xcloner_store_path();
            }
        }

        if ($suffix) {
            $path = $path.DS.".".$this->get_xcloner_tmp_path_suffix();
        }

        if (!is_dir($path)) {
            try {
                mkdir($path);
            } catch (Exception $e) {
                //silent catch
            }
        }

        return $path;
    }

    public function get_enable_mysql_backup()
    {
        if ($this->get_xcloner_option('xcloner_enable_mysql_backup')) {
            return true;
        }

        return false;
    }

    /**
     * Get Backup Extension Name
     *
     * @param string $ext
     * @return string ( hash.(tar|tgz) )
     */
    public function get_backup_extension_name($ext = "")
    {
        if (!$ext) {
            if ($this->get_xcloner_option('xcloner_backup_compression_level')) {
                $ext = ".tgz";
            } else {
                $ext = ".tar";
            }
        }

        return ($this->get_hash()).$ext;
    }

    /**
     * @return string
     */
    public function get_hash()
    {
        return $this->hash;
    }

    /**
     * Set New Hash
     *
     * @param string $hash
     * @return self
     */
    public function set_hash($hash = "")
    {
        if ($hash === 'generate_hash' || !$hash) {
            $hash = $this->generate_new_hash();
        }

        if (substr($hash, 0, 1) != "-" && strlen($hash)) {
            $hash = "-".$hash;
        }

        $this->hash = substr($hash, 0, 6);

        return $this;
    }

    public function generate_new_hash()
    {
        $hash = "-".md5(rand());

        return substr($hash, 0, 6);
    }

    /**
     * Get Default Backup Name
     *
     * @return string
     */
    public function get_default_backup_name()
    {
        $data = parse_url(get_site_url());

        return "backup_[domain]".(isset($data['port']) ? "_".$data['port'] : "")."-[time]-".($this->get_enable_mysql_backup() ? "sql" : "nosql");
    }

    /**
     * @param string $option_name
     */
    public function get_xcloner_option($option_name, $default = null)
    {
        return get_option($option_name, $default);
    }

    /**
     * Get Server Unique Hash used to generate the unique backup name
     *
     * @param integer $strlen
     * @return string
     */
    public function get_server_unique_hash($strlen = 0)
    {
        $hash = md5(get_home_url().__DIR__.$this->get_xcloner_encryption_key());

        if ($strlen) {
            $hash = substr($hash, 0, $strlen);
        }

        return $hash;
    }

    public function settings_init()
    {
        if (!$this->get_xcloner_option('xcloner_cron_settings_page')) {
            update_option('xcloner_cron_settings_page', '');
        }

        if (!$this->get_xcloner_option('xcloner_system_settings_page')) {
            update_option('xcloner_system_settings_page', '');
        }

        if (!$this->get_xcloner_option('xcloner_cleanup_settings_page')) {
            update_option('xcloner_cleanup_settings_page', '');
        }


        //ADDING SETTING SECTIONS
        //GENERAL section
        add_settings_section(
            'xcloner_general_settings_group',
            __(' '),
            array($this, 'xcloner_settings_section_cb'),
            'xcloner_settings_page'
        );

        //SYSTEM section
        add_settings_section(
            'xcloner_system_settings_group',
            __('These are advanced options recommended for developers!', 'xcloner-backup-and-restore'),
            array($this, 'xcloner_settings_section_cb'),
            'xcloner_system_settings_page'
        );

        //CLEANUP section
        /*add_settings_section(
            'xcloner_cleanup_settings_group',
            __(' '),
            array($this, 'xcloner_settings_section_cb'),
            'xcloner_cleanup_settings_page'
        );*/


        //CRON section
        add_settings_section(
            'xcloner_cron_settings_group',
            __(' '),
            array($this, 'xcloner_settings_section_cb'),
            'xcloner_cron_settings_page'
        );


        //REGISTERING THE 'GENERAL SECTION' FIELDS
        register_setting('xcloner_general_settings_group', 'xcloner_backup_compression_level', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_backup_compression_level',
            __('Backup Compression Level', 'xcloner-backup-and-restore'),
            array($this, 'do_form_range_field'),
            'xcloner_settings_page',
            'xcloner_general_settings_group',
            array(
                'xcloner_backup_compression_level',
                __('Options between [0-9]. Value 0 means no compression, while 9 is maximum compression affecting cpu load', 'xcloner-backup-and-restore'),
                0,
                9
            )
        );

        register_setting('xcloner_general_settings_group', 'xcloner_encryption_key', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_string"
        ));
        add_settings_field(
            'xcloner_encryption_key',
            __('Backup Encryption Key', 'xcloner-backup-and-restore'),
            array($this, 'do_form_text_field'),
            'xcloner_settings_page',
            'xcloner_general_settings_group',
            array(
                'xcloner_encryption_key',
                __('Backup Encryption Key used to Encrypt/Decrypt backups, you might want to save this somewhere else as well.', 'xcloner-backup-and-restore'),
                $this->get_xcloner_encryption_key(),
                //'disabled'
            )
        );

        register_setting('xcloner_general_settings_group', 'xcloner_enable_log', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_enable_log',
            __('Enable XCloner Backup Log', 'xcloner-backup-and-restore'),
            array($this, 'do_form_switch_field'),
            'xcloner_settings_page',
            'xcloner_general_settings_group',
            array(
                'xcloner_enable_log',
                sprintf(__('Enable the XCloner Backup log. You will find it stored unde the Backup Storage Location, file %s', 'xcloner-backup-and-restore'), $this->get_logger_filename())
            )
        );

        register_setting('xcloner_general_settings_group', 'xcloner_enable_pre_update_backup', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_enable_pre_update_backup',
            __('Generate Backups before Automatic WP Upgrades', 'xcloner-backup-and-restore'),
            array($this, 'do_form_switch_field'),
            'xcloner_settings_page',
            'xcloner_general_settings_group',
            array(
                'xcloner_enable_pre_update_backup',
                sprintf(__('Attempt to generate a full site backup before applying automatic core updates.', 'xcloner-backup-and-restore'), $this->get_logger_filename())
            )
        );

        register_setting('xcloner_general_settings_group', 'xcloner_regex_exclude', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_raw"
        ));
        add_settings_field(
            'xcloner_regex_exclude',
            __('Regex Exclude Files', 'xcloner-backup-and-restore'),
            array($this, 'do_form_textarea_field'),
            'xcloner_settings_page',
            'xcloner_general_settings_group',
            array(
                'xcloner_regex_exclude',
                __('Regular expression match to exclude files and folders, example patterns provided below, one pattern per line', 'xcloner-backup-and-restore'),
                //$this->get_xcloner_store_path(),
                //'disabled'
            )
        );

        //REGISTERING THE 'SYSTEM SECTION' FIELDS
        register_setting('xcloner_system_settings_group', 'xcloner_size_limit_per_request', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_size_limit_per_request',
            __('Data Size Limit Per Request', 'xcloner-backup-and-restore'),
            array($this, 'do_form_range_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_size_limit_per_request',
                __('Use this option to set how much file data can XCloner backup in one AJAX request. Range 0-1024 MB', 'xcloner-backup-and-restore'),
                0,
                1024
            )
        );

        register_setting('xcloner_system_settings_group', 'xcloner_files_to_process_per_request', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_files_to_process_per_request',
            __('Files To Process Per Request', 'xcloner-backup-and-restore'),
            array($this, 'do_form_range_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_files_to_process_per_request',
                __('Use this option to set how many files XCloner should process at one time before doing another AJAX call', 'xcloner-backup-and-restore'),
                0,
                1000
            )
        );

        register_setting('xcloner_system_settings_group', 'xcloner_directories_to_scan_per_request', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_directories_to_scan_per_request',
            __('Directories To Scan Per Request', 'xcloner-backup-and-restore'),
            array($this, 'do_form_range_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_directories_to_scan_per_request',
                __('Use this option to set how many directories XCloner should scan at one time before doing another AJAX call', 'xcloner-backup-and-restore'),
                0,
                1000
            )
        );

        register_setting('xcloner_system_settings_group', 'xcloner_database_records_per_request', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_database_records_per_request',
            __('Database Records Per Request', 'xcloner-backup-and-restore'),
            array($this, 'do_form_range_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_database_records_per_request',
                __('Use this option to set how many database table records should be fetched per AJAX request, or set to 0 to fetch all.  Range 0-100000 records', 'xcloner-backup-and-restore'),
                0,
                100000
            )
        );

        /*register_setting('xcloner_system_settings_group', 'xcloner_diff_backup_recreate_period', array($this->xcloner_sanitization, "sanitize_input_as_int"));
        add_settings_field(
            'xcloner_diff_backup_recreate_period',
           __('Differetial Backups Max Days','xcloner-backup-and-restore'),
            array($this, 'do_form_number_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array('xcloner_diff_backup_recreate_period',
             __('Use this option to set when a full backup should be recreated if the scheduled backup type is set to \'Full Backup+Differential Backups\' ','xcloner-backup-and-restore'),
             )
        );*/

        register_setting('xcloner_system_settings_group', 'xcloner_exclude_files_larger_than_mb', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_exclude_files_larger_than_mb',
            __('Exclude files larger than (MB)', 'xcloner-backup-and-restore'),
            array($this, 'do_form_number_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_exclude_files_larger_than_mb',
                __('Use this option to automatically exclude files larger than a certain size in MB, or set to 0 to include all. Range 0-1000 MB', 'xcloner-backup-and-restore'),
            )
        );

        register_setting('xcloner_system_settings_group', 'xcloner_split_backup_limit', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_split_backup_limit',
            __('Split Backup Archive Limit (MB)', 'xcloner-backup-and-restore'),
            array($this, 'do_form_number_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_split_backup_limit',
                __('Use this option to automatically split the backup archive into smaller parts. Range  0-10000 MB', 'xcloner-backup-and-restore'),
            )
        );

        register_setting('xcloner_system_settings_group', 'xcloner_force_tmp_path_site_root');
        add_settings_field(
            'xcloner_force_tmp_path_site_root',
            __('Force Temporary Path Within XCloner Storage', 'xcloner-backup-and-restore'),
            array($this, 'do_form_switch_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_force_tmp_path_site_root',
                __('Enable this option if you want the XCloner Temporary Path to be within your XCloner Storage Location', 'xcloner-backup-and-restore')
            )
        );

        register_setting('xcloner_system_settings_group', 'xcloner_disable_email_notification');
        add_settings_field(
            'xcloner_disable_email_notification',
            __('Disable Email Notifications', 'xcloner-backup-and-restore'),
            array($this, 'do_form_switch_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_disable_email_notification',
                __('Enable this option if you want the XCloner to NOT send email notifications on successful backups', 'xcloner-backup-and-restore')
            )
        );

        register_setting('xcloner_system_settings_group', 'xcloner_restore_defaults');
        add_settings_field(
            'xcloner_restore_defaults',
            __('Restore Default Settings', 'xcloner-backup-and-restore'),
            array($this, 'do_form_switch_field'),
            'xcloner_system_settings_page',
            'xcloner_system_settings_group',
            array(
                'xcloner_restore_defaults',
                __('This option will completelly remove all existing XCloner options and set them back to a default state.', 'xcloner-backup-and-restore')
            )
        );

        //REGISTERING THE 'CLEANUP SECTION' FIELDS
        /*register_setting('xcloner_cleanup_settings_group', 'xcloner_cleanup_retention_limit_days', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_cleanup_retention_limit_days',
            __('Cleanup by Age (days)', 'xcloner-backup-and-restore'),
            array($this, 'do_form_number_field'),
            'xcloner_cleanup_settings_page',
            'xcloner_cleanup_settings_group',
            array(
                'xcloner_cleanup_retention_limit_days',
                __('Specify the maximum number of days a backup archive can be kept on the server. 0 disables this option', 'xcloner-backup-and-restore')
            )
        );

        register_setting('xcloner_cleanup_settings_group', 'xcloner_cleanup_retention_limit_archives', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_cleanup_retention_limit_archives',
            __('Cleanup by Quantity', 'xcloner-backup-and-restore'),
            array($this, 'do_form_number_field'),
            'xcloner_cleanup_settings_page',
            'xcloner_cleanup_settings_group',
            array(
                'xcloner_cleanup_retention_limit_archives',
                __('Specify the maximum number of backup archives to keep on the server. 0 disables this option', 'xcloner-backup-and-restore')
            )
        );

        register_setting('xcloner_cleanup_settings_group', 'xcloner_cleanup_exclude_days', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_string"
        ));
        add_settings_field(
            'xcloner_cleanup_exclude_days',
            __('Keep Backups Taken On Month Days', 'xcloner-backup-and-restore'),
            array($this, 'do_form_text_field'),
            'xcloner_cleanup_settings_page',
            'xcloner_cleanup_settings_group',
            array(
                'xcloner_cleanup_exclude_days',
                __('Never delete backups taken on the specified days of the month, value used can be comma separated.', 'xcloner-backup-and-restore')
            )
        );

        register_setting('xcloner_cleanup_settings_group', 'xcloner_cleanup_capacity_limit', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_cleanup_capacity_limit',
            __('Cleanup by Capacity(MB)', 'xcloner-backup-and-restore'),
            array($this, 'do_form_number_field'),
            'xcloner_cleanup_settings_page',
            'xcloner_cleanup_settings_group',
            array(
                'xcloner_cleanup_capacity_limit',
                __('Remove oldest backups if all created backups exceed the configured limit in Megabytes. 0 disables this option', 'xcloner-backup-and-restore')
            )
        );*/

        /*
        //deprecated setting
        register_setting('xcloner_cleanup_settings_group', 'xcloner_cleanup_delete_after_remote_transfer', array(
            $this->xcloner_sanitization,
            "sanitize_input_as_int"
        ));
        add_settings_field(
            'xcloner_cleanup_delete_after_remote_transfer',
            __('Delete Backup After Remote Storage Transfer', 'xcloner-backup-and-restore'),
            array($this, 'do_form_switch_field'),
            'xcloner_cleanup_settings_page',
            'xcloner_cleanup_settings_group',
            array(
                'xcloner_cleanup_delete_after_remote_transfer',
                __('Remove backup created automatically from local storage after sending the backup to Remote Storage', 'xcloner-backup-and-restore')
            )
        );
        */

        //REGISTERING THE 'CRON SECTION' FIELDS
        register_setting('xcloner_cron_settings_group', 'xcloner_cron_frequency');
        add_settings_field(
            'xcloner_cron_frequency',
            __('Cron frequency', 'xcloner-backup-and-restore'),
            array($this, 'do_form_text_field'),
            'xcloner_cron_settings_page',
            'xcloner_cron_settings_group',
            array(
                'xcloner_cron_frequency',
                __('Cron frequency')
            )
        );
    }




    /**
     * callback functions
     */

    // section content cb
    public function xcloner_settings_section_cb()
    {
        //echo '<p>WPOrg Section Introduction.</p>';
    }

    // text field content cb
    public function do_form_text_field($params)
    {
        if (!isset($params['3'])) {
            $params[3] = 0;
        }
        if (!isset($params['2'])) {
            $params[2] = 0;
        }

        list($fieldname, $label, $value, $disabled) = $params;

        if (!$value) {
            $value = $this->get_xcloner_option($fieldname);
        }
        // output the field?>
        <div class="row">
            <?php if ($params[0] !== 'xcloner_cleanup_exclude_days'):?>
            <div class="input-field col s10 m10 l8">
            <?php else:?>
            <div class="input-field col s10 m5 l3">
            <?php endif?>
                <input class="validate" <?php echo ($disabled) ? "disabled" : "" ?> name="<?php echo esc_attr($fieldname) ?>"
                       id="<?php echo esc_attr($fieldname) ?>" type="text" class="validate"
                       value="<?php echo isset($value) ? esc_attr($value) : ''; ?>">
            </div>
            <div class="col s2 m2 ">
                <a class="btn-floating tooltipped btn-small" data-position="center" data-delay="50"
                   data-tooltip="<?php echo esc_attr($label) ?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
            </div>
        </div>


		<?php
    }

    /**
     * Password field UI
     *
     * @param [type] $params
     * @return void
     */
    public function do_form_password_field($params)
    {
        if (!isset($params['3'])) {
            $params[3] = 0;
        }
        if (!isset($params['2'])) {
            $params[2] = 0;
        }

        list($fieldname, $label, $value, $disabled) = $params;

        if (!$value) {
            $value = $this->get_xcloner_option($fieldname);
        }
        // output the field?>
        <div class="row">
            <div class="input-field col s10 m10 l8">
                <input class="validate" <?php echo ($disabled) ? "disabled" : "" ?> name="<?php echo esc_attr($fieldname) ?>"
                       id="<?php echo esc_attr($fieldname) ?>" type="password" class="validate"
                       value="<?php echo isset($value) ? esc_attr($value) : ''; ?>">
            </div>
            <div class="col s2 m2 ">
                <a class="btn-floating tooltipped btn-small" data-position="left" data-delay="50"
                   data-tooltip="<?php echo esc_attr($label) ?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
            </div>
        </div>


		<?php
    }

    // textarea field content cb
    public function do_form_textarea_field($params)
    {
        if (!isset($params['3'])) {
            $params[3] = 0;
        }
        if (!isset($params['2'])) {
            $params[2] = 0;
        }

        list($fieldname, $label, $value, $disabled) = $params;

        if (!$value) {
            $value = $this->get_xcloner_option($fieldname);
        }
        // output the field?>
        <div class="row">
            <div class="input-field col s10 m10 l8">
                <textarea class="validate" <?php echo ($disabled) ? "disabled" : "" ?> name="<?php echo esc_attr($fieldname) ?>"
                          id="<?php echo esc_attr($fieldname) ?>" type="text" class="validate"
                          value=""><?php echo isset($value) ? esc_attr($value) : ''; ?></textarea>
            </div>
            <div class="col s2 m2 ">
                <a class="btn-floating tooltipped btn-small" data-position="center" data-html="true" data-delay="50"
                   data-tooltip="<?php echo esc_attr($label) ?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
            </div>
            <div class="col s12">
                <ul class="xcloner_regex_exclude_limit">
                    <li>Exclude all except .php file: <span
                                class="regex_pattern"><?php echo htmlentities('(.*)\.(.+)$(?<!(php))') ?></span></li>
                    <li>Exclude all except .php and .txt: <span
                                class="regex_pattern"> <?php echo htmlentities('(.*)\.(.+)$(?<!(php|txt))') ?></span>
                    </li>
                    <li>Exclude all .avi files: <span
                                class="regex_pattern"> <?php echo htmlentities('(.*)\.(.+)$(?<=(avi))') ?></span></li>
                    <li>Exclude all .jpg,.gif and .png files: <span
                                class="regex_pattern"> <?php echo htmlentities('(.*)\.(.+)$(?<=(gif|png|jpg))') ?></span>
                    </li>
                    <li>Exclude all .svn and .git: <span
                                class="regex_pattern"> <?php echo htmlentities('(.*)\.(svn|git)(.*)$') ?></span></li>
                    <li>Exclude root directory /test: <span
                                class="regex_pattern"> <?php echo htmlentities('\/test(.*)$') ?></span> or <span
                                class="regex_pattern"> <?php echo htmlentities('test(.*)$') ?></span></li>
                    <li>Exclude the wp-admin folder: <span
                                class="regex_pattern"> <?php echo htmlentities('(\/wp-admin)(.*)$') ?></span></li>
                    <li>Exclude the wp-content/uploads folder: <span
                                class="regex_pattern"> <?php echo htmlentities('(\/wp-content\/uploads)(.*)$') ?></span>
                    </li>
                    <li>Exclude the wp-admin, wp-includes and wp-config.php: <span
                                class="regex_pattern"> <?php echo htmlentities('\/(wp-admin|wp-includes|wp-config.php)(.*)$') ?></span>
                    </li>
                    <li>Exclude wp-content/updraft and wp/content/uploads/wp_all_backup folder :<span
                                class="regex_pattern">\/(wp-content\/updraft|\/wp-content\/uploads\/wp_all_backup)(.*)$</span>
                    </li>
                    <li>Exclude all cache folders from wp-content/ and it's subdirectories: <span
                                class="regex_pattern"> <?php echo htmlentities('\/wp-content(.*)\/cache($|\/)(.*)') ?></span>
                    </li>
                    <li>Exclude wp-content/cache/ folder: <span
                                class="regex_pattern"> <?php echo htmlentities('\/wp-content\/cache(.*)') ?></span>
                    </li>
                    <li>Exclude all error_log files: <span
                                class="regex_pattern"> <?php echo htmlentities('(.*)error_log$') ?></span></li>
                </ul>
            </div>
        </div>


		<?php
    }

    // number field content cb
    public function do_form_number_field($params)
    {
        if (!isset($params['3'])) {
            $params[3] = 0;
        }
        if (!isset($params['2'])) {
            $params[2] = 0;
        }

        list($fieldname, $label, $value, $disabled) = $params;

        if (!$value) {
            $value = $this->get_xcloner_option($fieldname);
        }
        // output the field?>
        <div class="row">
            <div class="input-field col s10 m5 l3">
                <input class="validate" <?php echo ($disabled) ? "disabled" : "" ?> name="<?php echo esc_attr($fieldname) ?>"
                       id="<?php echo esc_attr($fieldname) ?>" type="number" class="validate"
                       value="<?php echo isset($value) ? esc_attr($value) : ''; ?>">
            </div>
            <div class="col s2 m2 ">
                <a class="btn-floating tooltipped btn-small" data-html="true" data-position="center" data-delay="50"
                   data-tooltip="<?php echo esc_attr($label) ?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
            </div>
        </div>


		<?php
    }

    public function do_form_range_field($params)
    {
        if (!isset($params['4'])) {
            $params[4] = 0;
        }

        list($fieldname, $label, $range_start, $range_end, $disabled) = $params;
        $value = $this->get_xcloner_option($fieldname); ?>
        <div class="row">
            <div class="input-field col s10 m10 l8">
                <p class="range-field">
                    <input <?php echo ($disabled) ? "disabled" : "" ?> type="range" name="<?php echo esc_attr($fieldname) ?>"
                                                                         id="<?php echo esc_attr($fieldname) ?>"
                                                                         min="<?php echo esc_attr($range_start) ?>"
                                                                         max="<?php echo esc_attr($range_end) ?>"
                                                                         value="<?php echo isset($value) ? esc_attr($value) : ''; ?>"/>
                </p>
            </div>
            <div class="col s2 m2 ">
                <a class="btn-floating tooltipped btn-small" data-html="true" data-position="center" data-delay="50"
                   data-tooltip="<?php echo esc_attr($label) ?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
            </div>
        </div>
		<?php
    }


    public function do_form_switch_field($params)
    {
        if (!isset($params['2'])) {
            $params[2] = 0;
        }
        list($fieldname, $label, $disabled) = $params;
        $value = $this->get_xcloner_option($fieldname); ?>
        <div class="row">
            <div class="input-field col s10 m5 l3">
                <div class="switch">
                    <label>
                        Off
                        <input <?php echo ($disabled) ? "disabled" : "" ?> type="checkbox"
                                                                             name="<?php echo esc_attr($fieldname) ?>"
                                                                             id="<?php echo esc_attr($fieldname) ?>"
                                                                             value="1" <?php echo ($value) ? 'checked="checked"' : ''; ?>
                        ">
                        <span class="lever"></span>
                        On
                    </label>
                </div>
            </div>
            <div class="col s2 m2">
                <a class="btn-floating tooltipped btn-small" data-position="center" data-delay="50"
                   data-tooltip="<?php echo esc_attr($label) ?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
            </div>
        </div>
		<?php
    }
}
