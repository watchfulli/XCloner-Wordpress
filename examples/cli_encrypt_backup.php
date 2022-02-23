<?php
/**
 * USAGE
 * php cli_encrypt_backup.php BACKUP_NAME --encrypt(--decrypt) --key=ENCRYPTION_KEY_OPTIONAL
 */

define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);

if(file_exists(__DIR__ . "/../../../../wp-load.php")) {
    require_once(__DIR__ .'/../../../../wp-load.php');
}

if (isset($argv[1])) {
    $backup_name = $argv[1];
}

if (isset($argv[2]) && $argv[2] == '--encrypt') {
    $cmd = "encrypt";
}

if (isset($argv[2]) && $argv[2] == '--decrypt') {
    $cmd = "decrypt";
}

$key = "";

if (isset($argv[3]) && substr($argv[3], 0, 6) == '--key=') {
    $key = substr($argv[3], 6, strlen($argv[3]));
}

require_once(plugin_dir_path(__FILE__).'/../vendor/autoload.php');

//require_once(dirname(__DIR__) . '/includes/class-xcloner-standalone.php');

//loading the default xcloner settings in format [{'option_name':'value', {'option_value': 'value'}}]
$json_config = json_decode(file_get_contents(__DIR__ . '/standalone_backup_trigger_config.json'));

if (!$json_config) {
    die('Could not parse default JSON config, i will shutdown for now...');
}

//pass json config to Xcloner_Standalone lib
$xcloner = new Watchfulli\XClonerCore\Xcloner_Standalone($json_config);

if (!$backup_name) {
    $return = $xcloner->start();
    $backup_name = $return['extra']['backup_archive_name_full'];
}

$backup_path = $xcloner->get_xcloner_filesystem()->get_storage_path_file_info($backup_name)->getPathName();
if (!file_exists($backup_path)) {
    die(sprintf("Backup file %s does not exists.\n", $backup_path));
}

if (isset($cmd) && $cmd == "encrypt") {
    $xcloner->get_xcloner_encryption()->encrypt_file($backup_name, "encrypted_".$backup_name, $key);
} elseif (isset($cmd) && $cmd == "decrypt") {
    $xcloner->get_xcloner_encryption()->decrypt_file($backup_name, "decrypted_".$backup_name, $key);
}

if (!isset($cmd)) {
    if ($xcloner->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
        echo sprintf("Backup %s is encrypted, i will try to decrypt it \n", $backup_name);
        $xcloner->get_xcloner_encryption()->decrypt_file($backup_name, "decrypted_".$backup_name, $key);
    } else {
        echo sprintf("Backup %s is not encrypted, i will try to encrypt it \n", $backup_name);
        $xcloner->get_xcloner_encryption()->encrypt_file($backup_name, "encrypted_".$backup_name, $key);
    }
}
