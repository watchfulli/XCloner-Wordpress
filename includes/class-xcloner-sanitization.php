<?php
use League\Flysystem\Util;

class Xcloner_Sanitization {
	
	public function __construct(){}
	
	public function sanitize_input_as_int($option)
	{
		return filter_var($option, FILTER_SANITIZE_NUMBER_INT);
	}
	
	public function sanitize_input_as_float($option)
	{
		return filter_var($option, FILTER_VALIDATE_FLOAT);
	}
	
	public function sanitize_input_as_string($option)
	{
		return filter_var($option, FILTER_SANITIZE_STRING);
	}
	
	public function sanitize_input_as_absolute_path($option)
	{
		$path = filter_var($option, FILTER_SANITIZE_URL);
		
		try{
			$option = Util::normalizePath($path);
		}catch(Exception $e){
			add_settings_error('xcloner_error_message', '', __($e->getMessage()), 'error');
		}
		
		if($path and !is_dir($path)){
			add_settings_error('xcloner_error_message', '', __(sprintf('Invalid Server Path %s',$option)), 'error');
			return false;
		}
		
		return $path;
	}
	
	public function sanitize_input_as_path($option)
	{
		return filter_var($option, FILTER_SANITIZE_URL);
	}
	
	public function sanitize_input_as_relative_path($option)
	{
		$option = filter_var($option, FILTER_SANITIZE_URL);
		$option = str_replace("..", "", $option);
		
		return $option;
	}
	
	public function sanitize_input_as_email($option)
	{
		return filter_var($option, FILTER_SANITIZE_EMAIL);
	}

	public function sanitize_input_as_raw($option)
	{
		return filter_var($option, FILTER_UNSAFE_RAW);
	}
	
}
