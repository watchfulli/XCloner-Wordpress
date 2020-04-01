<?php

define('XCLONER_STANDALONE_MODE', true);

require_once(__DIR__.'/../vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

include_once(__DIR__ ."/../lib/mock_wp_functions.php");
require_once(__DIR__ . '/../includes/class-xcloner.php');


class Xcloner_Standalone extends Xcloner
{
    public function __construct($json_config)
    {
        if (WP_DEBUG && WP_DEBUG_DISPLAY) {
            $this->log_php_errors();
        }

        $this->load_dependencies();
        
        $this->xcloner_settings = new XCloner_Settings($this, "", $json_config);
        
        $this->xcloner_settings->generate_new_hash();
        
        $this->xcloner_logger           = new XCloner_Logger($this, "xcloner_standalone");

        if (WP_DEBUG && WP_DEBUG_DISPLAY) {
            $this->xcloner_logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        }


        $this->xcloner_filesystem       = new Xcloner_File_System($this);
        $this->archive_system           = new Xcloner_Archive($this);
        $this->xcloner_database         = new Xcloner_Database($this);
        $this->xcloner_scheduler        = new Xcloner_Scheduler($this);
        $this->xcloner_remote_storage   = new Xcloner_Remote_Storage($this);
        $this->xcloner_file_transfer 	= new Xcloner_File_Transfer($this);
        $this->xcloner_encryption    	= new Xcloner_Encryption($this);
        
        //$this->start();
    }

    /**
     * Start backup process trigger method
     *
     * @return void
     */
    public function start()
    {
        $schedule_config = ($this->xcloner_settings->get_xcloner_option('schedule'));

        $data['params']                 = "";
        $data['backup_params']          = $schedule_config->backup_params;
        $data['table_params']           = json_encode($schedule_config->database);
        $data['excluded_files']         = json_encode($schedule_config->excluded_files);

        return $this->xcloner_scheduler->xcloner_scheduler_callback(null, $data, $this);
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

    /**
     * Get Xcloner Main Class Container
     *
     * @return void
     */
    private function get_xcloner_container()
    {
        return $this;
    }
}
