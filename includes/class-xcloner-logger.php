<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Xcloner_Logger extends Logger{
	
	public function __construct($logger_name = "xcloner_logger")
	{
		
		$xcloner_settings = new Xcloner_Settings();
		
		$logger_path = $xcloner_settings->get_xcloner_store_path().DS.$xcloner_settings->get_logger_filename();
		
		if(!$xcloner_settings->get_xcloner_option('xcloner_enable_log'))
			$logger_path = "php://stderr";
		
		// create a log channel
		parent::__construct($logger_name);
		
		$debug_level = Logger::INFO;
		
		//if(WP_DEBUG)
		//	$debug_level = Logger::DEBUG;
		
		$this->pushHandler(new StreamHandler($logger_path, $debug_level));
		
		
		//$this->info("Starting logger");
		return $this;
	}
}
