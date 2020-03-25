<?php
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);

require_once(__DIR__ . '/includes/class-xcloner-standalone.php');

//loading the default xcloner settings in format [{'option_name':'value', {'option_value': 'value'}}]
$json_config = json_decode(file_get_contents(__DIR__ . '/json_config_standalone_sample.txt'));

if (!$json_config) {
    die('Could not parse default JSON config, i will shutdown for now...');
}
new Xcloner_Standalone($json_config);
