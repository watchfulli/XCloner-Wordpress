<?php
$wp_settings_errors = "";


//NONCE_KEY
//NONCE_SALT
//ABSPATH

if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}

if (!defined('MINUTE_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
}

if (!defined('MINUTE_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
}

if (!defined('MINUTE_IN_SECONDS')) {
    define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
}

if (!defined('MINUTE_IN_SECONDS')) {
    define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
}

if (!defined('MINUTE_IN_SECONDS')) {
    define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);
}

if (!defined('MINUTE_IN_SECONDS')) {
    define('WPINC', true);
}

/**
 * Undocumented function
 *
 * @param [type] $setting
 * @param [type] $code
 * @param [type] $message
 * @param string $type
 * @return void
 */
if (!function_exists('add_settings_error')) {
    function add_settings_error($setting, $code, $message, $type = 'error')
    {
        global $wp_settings_errors;
        
        $wp_settings_errors[] = array(
                        'setting' => $setting,
                        'code'    => $code,
                        'message' => $message,
                        'type'    => $type,
                );
    }
}
/**
 * Get option
 *
 * @param [type] $option_name
 * @return void
 */
if (!function_exists('get_option')) {
    function get_option($option_name)
    {
    }
}

/**
 * Add option
 *
 * @param [type] $option_name
 * @param string $value
 * @return void
 */
if (!function_exists('add_option')) {
    function add_option($option_name, $value="")
    {
    }
}

/**
 * Update option or create if it doesn't exist
 *
 * @param [type] $option_name
 * @param string $value
 * @return void
 */
if (!function_exists('update_option')) {
    function update_option($option_name, $value="")
    {
    }
}

/**
 * Die script
 */
if (!function_exists('wp_die')) {
    function wp_die($msg)
    {
        die($msg);
    }
}

/**
 *
 */
if (!function_exists('dbDelta')) {
    function dbDelta($sql)
    {
    }
}

/**
 *
 */
if (!function_exists('is_admin')) {
    function is_admin()
    {
    }
}

/**
 *
 */
if (!function_exists('register_activation_hook')) {
    function register_activation_hook($path, $hook)
    {
    }
}

/**
 *
 */
if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($path, $hook)
    {
    }
}

/**
 *
 */
if (!function_exists('deactivate_plugins')) {
    function deactivate_plugins($path)
    {
    }
}

/**
 *
 */
if (!function_exists('wp_deregister_script')) {
    function wp_deregister_script($path)
    {
    }
}


/**
 *
 */
if (!function_exists('add_action')) {
    function add_action($path)
    {
    }
}

/**
 *
 */
if (!function_exists('do_action')) {
    function do_action($hook)
    {
    }
}

/**
 *
 */
if (!function_exists('add_filter')) {
    function add_action($path)
    {
    }
}

/**
 *
 */
if (!function_exists('do_filter')) {
    function do_filter($hook)
    {
    }
}

/**
 *
 */
if (!function_exists('_e')) {
    function _e($str)
    {
        return $str;
    }
}

/**
 *
 */
if (!function_exists('plugin_basename')) {
    function plugin_basename($path)
    {
        return $path;
    }
}

/**
 *
 */
if (!function_exists('settings_error')) {
    function settings_error($error)
    {
        return $error;
    }
}

/**
 *
 */
if (!function_exists('add_menu_page')) {
    function add_menu_page()
    {

    }
}

// function current_user_can(){}
// function sanitize_key(){}
// function plugin_dir_url() {}
// function human_time_diff() {}
// function size_format(){}
// function wp_get_schedules() {}
// function wp_send_json() {}
// function get_site_url() {}
// function get_home_url() {}
// function wp_mail() {}
// function __(){}
// function admin_url(){}
// function load_plugin_textdomain() {}
// function wp_clear_scheduled_hook() {}
// function wp_unschedule_event() {}
// function wp_schedule_event() {}
// function wp_next_scheduled() {}
// function wp_schedule_single_event() {}
// function add_settings_section() {}
// function register_setting() {}