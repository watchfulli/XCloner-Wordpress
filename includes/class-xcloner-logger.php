<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Xcloner_Logger extends Logger{
	
	private $logger_path ;
	
	public function __construct($logger_name = "xcloner_logger")
	{
		$xcloner_settings 	= new Xcloner_Settings();
		
		$logger_path = $xcloner_settings->get_xcloner_store_path().DS.$xcloner_settings->get_logger_filename();
		
		$this->logger_path = $logger_path;
		
		if(!is_dir($xcloner_settings->get_xcloner_store_path()) or !is_writable($xcloner_settings->get_xcloner_store_path()))
			return;
		
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
	
	function getLastDebugLines($totalLines = 200) 
	{
		$lines = array();
		
		if(!file_exists($this->logger_path) or !is_readable($this->logger_path))
			return false;
		
		$fp = fopen($this->logger_path, 'r');
		fseek($fp, -1, SEEK_END);
		$pos = ftell($fp);
		$lastLine = "";
		
		// Loop backword until we have our lines or we reach the start
		while($pos > 0 && count($lines) < $totalLines) {
		
		$C = fgetc($fp);
		if($C == "\n") {
		  // skip empty lines
		  if(trim($lastLine) != "") {
			$lines[] = $lastLine;
		  }
		  $lastLine = '';
		} else {
		  $lastLine = $C.$lastLine;
		}
		fseek($fp, $pos--);
		}
		
		$lines = array_reverse($lines);
		
		return $lines;
	}
}
