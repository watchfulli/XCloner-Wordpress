<?php
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);

require_once(dirname(__DIR__) . '/includes/class-xcloner-standalone.php');

if (!is_admin()) {
    die('Access denied');
}

//loading the default xcloner settings in format [{'option_name':'value', {'option_value': 'value'}}]
$json_config = json_decode(file_get_contents(__DIR__ . '/standalone_config.json'));

if (!$json_config) {
    die('Could not parse default JSON config, i will shutdown for now...');
}

if (!is_localhost())
{
    if (!isset($_REQUEST['standalone_api_key']) || !$_REQUEST['standalone_api_key'] || $config['xcloner_standalone_api_key'] != $_REQUEST['standalone_api_key']) {
        die('Access denied, please check your standalone_api_key value');
    }
}

$config = [];

foreach ($json_config as $item) {
    $config[$item->option_name] = $item->option_value;
}

//we check to see if we have a backup_profile sent over post
if (isset($_POST['backup_profile']) && trim($_POST['backup_profile'])) {
    $arr = json_decode(stripslashes($_POST['backup_profile']), true);
    $config['profile'] = array_combine(array_keys($arr), $arr);
    $config['profile']['processed'] = true;
    $_POST['data'] = json_encode($config['profile']);
}

if (isset($_POST['extra'])) {
    $_POST['data'] = json_decode(stripslashes($_POST['data']));
    $_POST['data']->extra = json_decode(($_POST['extra']));
    $_POST['data'] = json_encode($_POST['data']);
}

//pass json config to Xcloner_Standalone lib
$xcloner_backup = new Xcloner_Standalone($json_config);
ob_end_clean();

$xcloner_backup->define_ajax_hooks();
$xcloner_backup->run();
