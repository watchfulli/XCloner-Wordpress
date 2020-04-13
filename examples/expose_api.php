<?php
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);

require_once(dirname(__DIR__) . '/includes/class-xcloner-standalone.php');

if(!is_admin()){
    die('Access denied');
}

//loading the default xcloner settings in format [{'option_name':'value', {'option_value': 'value'}}]
$json_config = json_decode(file_get_contents(__DIR__ . '/standalone_backup_trigger_config.json'));

if (!$json_config) {
    die('Could not parse default JSON config, i will shutdown for now...');
}

if(!is_localhost()) {
    if( !isset($_REQUEST['standalone_api_key']) || $json_config['xcloner_standalone_api_key'] != $_REQUEST['standalone_api_key']) {
        die('Access denied, please check your standalone_api_key value');
    }
}

if(isset($_POST['extra'])) {
    $_POST['data'] = json_decode(stripslashes($_POST['data']));
    $_POST['data']->extra = json_decode(($_POST['extra']));
    $_POST['data'] = json_encode($_POST['data']);
}

//pass json config to Xcloner_Standalone lib
$xcloner_backup = new Xcloner_Standalone($json_config);
ob_end_clean();

$xcloner_backup->define_ajax_hooks();
$xcloner_backup->run();

/*
$xcloner_api = new Xcloner_Api($xcloner_backup);
ob_end_clean();

//$_POST['file'] = "backup__8888-2020-04-01_14-03-sql-fab78.tar";
//$xcloner_api->list_backup_files();

$_POST['finished'] = 0;

$i=0;
while( !$_POST['finished'] ){
    
    $_POST['data'] =  json_encode(array());
    
    $data = $xcloner_api->scan_filesystem();

    $_POST['hash']  = $data['hash'];
    $_POST['init'] = 0;
    $_POST['finished'] = $data['finished'];
}


echo $xcloner_backup->xcloner_settings->get_hash();
*/