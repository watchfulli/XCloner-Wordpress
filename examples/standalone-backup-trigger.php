<?php
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);

require_once('../vendor/autoload.php');

$profile = [
    'id' => 0
];

//loading the default xcloner settings in format [{'option_name':'value', {'option_value': 'value'}}]
$json_config = json_decode(file_get_contents(__DIR__ . '/standalone_backup_trigger_config.json'));

if (!$json_config) {
    die('Could not parse default JSON config, i will shutdown for now...');
}

//pass json config to Xcloner_Standalone lib
$xcloner_backup = new Watchful\XClonerCore\Xcloner_Standalone($json_config);

$xcloner_backup->start($profile['id']);
