<?php
$wp_settings_errors = "";

//NONCE_KEY
//NONCE_SALT
//ABSPATH

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}

if (!defined('WPINC')) {
    define('WPINC', false);
}

if (!defined('DOING_CRON')) {
    define('DOING_CRON', false);
}

if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', false);
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__);
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

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

if(!class_exists('WP_Error')) {
    require_once(__DIR__ . '/class-wp-error.php');
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing)
    {
        return ($thing instanceof WP_Error);
    }
}

if (!function_exists('get_site_url')) {
    function get_site_url()
    {
        return __DIR__;
    }
}

if (!function_exists('admin_url')) {
    function admin_url()
    {
        return __DIR__;
    }
}

/**
 * Sanitize plugin dir path
 *
 * @param [type] $path
 * @return void
 */
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($path)
    {
        return dirname($path).DS;
    }
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

if (!function_exists('has_filter')) {
    function has_filter($tag, $function_to_check = false)
    {
        return false;
    }
}

if (!function_exists('mbstring_binary_safe_encoding')) {
    function mbstring_binary_safe_encoding($reset = false)
    {
        static $encodings  = array();
        static $overloaded = null;
 
        if (is_null($overloaded)) {
            $overloaded = function_exists('mb_internal_encoding') && (ini_get('mbstring.func_overload') & 2);
        }
 
        if (false === $overloaded) {
            return;
        }
 
        if (! $reset) {
            $encoding = mb_internal_encoding();
            array_push($encodings, $encoding);
            mb_internal_encoding('ISO-8859-1');
        }
 
        if ($reset && $encodings) {
            $encoding = array_pop($encodings);
            mb_internal_encoding($encoding);
        }
    }
}

if (!function_exists('reset_mbstring_encoding')) {
    function reset_mbstring_encoding()
    {
        mbstring_binary_safe_encoding(true);
    }
}

/**
 * Get option
 *
 * @param [type] $option_name
 * @return void
 */
if (!function_exists('get_option')) {
    function get_option($option_name = "")
    {
        if (!$option_name) {
            return $GLOBALS['xcloner_settings'];
        }

        if(!isset($GLOBALS['xcloner_settings'][$option_name])){
            return null;
        }

        return $GLOBALS['xcloner_settings'][$option_name];
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
        return $GLOBALS['xcloner_settings'][$option_name] = $value;
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
        return add_option($option_name, $value);
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
 * Custom Watchfull backend check
 */
if (!function_exists('is_admin')) {
    function is_admin()
    {
        return true;
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
    function add_action($hook, $callback)
    {   
        if(substr($hook, 0, 8) == "wp_ajax_") {
            $request = "wp_ajax_".$_REQUEST['action'];
            if($request === $hook){  
                //print_r($callback[1]);
                //exit;
                return call_user_func($callback);
            }
        }
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
    function add_filter($hook)
    {
        //echo $hook;
    }
}

/**
 *
 */
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value)
    {
        return $value;
    }
}

/**
 *
 */
if (!function_exists('do_filter')) {
    function do_filter2($hook)
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

/**
 * Get Home Url
 *
 * @return path
 */
if (!function_exists('get_home_url')) {
    function get_home_url()
    {
        return __DIR__;
    }
}

if (!function_exists('wp_load_translations_early')) {
    function wp_load_translations_early()
    {
        return null;
    }
}

/**
 * Translate string if available
 *
 * @return string
 */
if (!function_exists('__')) {
    function __($string)
    {
        return $string;
    }
}

if (!function_exists('wp_send_json')) {
    function wp_send_json( $response, $status_code = null ) {
        //return $response;
        die(json_encode($response));
    }
}

if (!function_exists('is_localhost')) {
    function is_localhost($whitelist = ['127.0.0.1', '::1']) {
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    }
}

if (!function_exists('esc_html')) {
    function esc_html( $text ) {
        return $text;
    }
}

if (!function_exists('size_format')) {
    function size_format( $bytes, $decimals = 0 ) {
       return $bytes;
    }
}
if (!function_exists('wp_mail')) {
    function wp_mail(){

    }
}

if (!function_exists('wp_debug_backtrace_summary')) {
    function wp_debug_backtrace_summary(){

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
