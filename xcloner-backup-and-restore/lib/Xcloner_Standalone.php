<?php

namespace Watchfulli\XClonerCore;

define('XCLONER_STANDALONE_MODE', true);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

include_once(__DIR__ . "/../lib/mock_wp_functions.php");

class Xcloner_Standalone extends Xcloner
{
    public function __construct($json_config = "")
    {
        if (WP_DEBUG && WP_DEBUG_DISPLAY) {
            $this->log_php_errors();
        }

        $this->load_dependencies();

        $this->define_plugin_settings($json_config);

        if (!isset($_POST['hash']) || !$_POST['hash']) {
            $_POST['hash'] = "";
        }
        $this->xcloner_settings = new Xcloner_Settings($this, $_POST['hash'], $json_config);


        if (!$this->xcloner_settings->get_hash(true)) {
            $this->xcloner_settings->generate_new_hash();
        }


        $this->define_plugin_settings();

        $this->xcloner_logger = new Xcloner_Logger($this, "xcloner_standalone");

        if (WP_DEBUG && WP_DEBUG_DISPLAY) {
            $this->xcloner_logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        }

        $this->xcloner_filesystem = new Xcloner_File_System($this);
        $this->archive_system = new Xcloner_Archive($this);
        $this->xcloner_database = new Xcloner_Database($this);
        $this->xcloner_scheduler = new Xcloner_Scheduler($this);
        $this->xcloner_remote_storage = new Xcloner_Remote_Storage($this);
        $this->xcloner_file_transfer = new Xcloner_File_Transfer($this);
        $this->xcloner_encryption = new Xcloner_Encryption($this);
        $this->xcloner_sanitization = new Xcloner_Sanitization();
    }

    /**
     * Start backup process trigger method
     *
     * @return void
     */
    public function start($profile_id)
    {
        $profile_config = ($this->xcloner_settings->get_xcloner_option('profile'));

        $data['params'] = "";
        $data['backup_params'] = $profile_config->backup_params;
        $data['table_params'] = json_encode($profile_config->database);
        $data['excluded_files'] = json_encode($profile_config->excluded_files);
        if (isset($profile_id) && $profile_id) {
            $data['id'] = $profile_id;
        }

        return $this->xcloner_scheduler->xcloner_scheduler_callback($data['id'], $data, $this);
    }

    /**
     * Overwrite parent __call method
     *
     * @param [type] $property
     * @param [type] $args
     * @return void
     */
    public function __call($property, $args)
    {
        $property = str_replace("get_", "", $property);

        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}
