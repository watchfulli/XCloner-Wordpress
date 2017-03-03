<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Xcloner_Logger extends Logger{
	
	private $logger_path ;
	private $max_logger_files = 15;
	private $main_logger_url;
	
	public function __construct($logger_name = "xcloner_logger", $hash="")
	{
		$xcloner_settings 	= new Xcloner_Settings($hash);
		$logger_path 		= $xcloner_settings->get_xcloner_store_path().DS.$xcloner_settings->get_logger_filename();
		$logger_path_tmp 	= "";
		
		if($hash)
			$logger_path_tmp = $xcloner_settings->get_xcloner_tmp_path().DS.$xcloner_settings->get_logger_filename(1);
		
		
		$this->logger_path = $logger_path;
		//$this->logger_path_tmp = $logger_path_tmp;
		
		if(!is_dir($xcloner_settings->get_xcloner_store_path()) or !is_writable($xcloner_settings->get_xcloner_store_path()))
		{
			$logger_path = "php://stderr";
			$logger_path_tmp = "";
		}
		
		if(!$xcloner_settings->get_xcloner_option('xcloner_enable_log'))
		{
			$logger_path = "php://stderr";
			$logger_path_tmp = "";
		}
		
		// create a log channel
		parent::__construct($logger_name);
		
		$debug_level = Logger::INFO;
		
		if(WP_DEBUG)
			$debug_level = Logger::DEBUG;

	
		if($logger_path)
		{
			$stream = new RotatingFileHandler($logger_path, $this->max_logger_files, $debug_level);
			$this->pushHandler($stream);
			
			$this->main_logger_url =  $stream->getUrl();
		}
			
		if($hash and $logger_path_tmp)
			$this->pushHandler(new StreamHandler($logger_path_tmp, $debug_level));
		
		return $this;
	}
	
	function get_main_logger_url()
	{
		return $this->main_logger_url;
	}
	
	function getLastDebugLines($totalLines = 200) 
	{
		$lines = array();
		
		//if(!file_exists($this->logger_path) or !is_readable($this->logger_path))
		if(!file_exists($this->main_logger_url) or !is_readable($this->main_logger_url))
			return false;
		
		//$fp = fopen($this->logger_path, 'r');
		$fp = fopen($this->main_logger_url, 'r');
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
