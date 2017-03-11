<?php

class Xcloner_Requirements
{
	
	var $min_php_version 	= "5.4.0";
	var $safe_mode			= "Off";
	
	private $xcloner_settings;
	private $xcloner_container;
	
	public function __construct(Xcloner $xcloner_container)
	{
		$this->xcloner_container = $xcloner_container;
		$this->xcloner_settings = $xcloner_container->get_xcloner_settings();
	}
	
	private function get_xcloner_container()
	{
		return $this->xcloner_container;
	}
	
	public function check_backup_ready_status()
	{
		if(!$this->check_min_php_version(1))
			return false;
		
		if(!$this->check_safe_mode(1))
			return false;
		
		if(!$this->check_xcloner_start_path(1))
			return false;
		
		if(!$this->check_xcloner_store_path(1))
			return false;
		
		if(!$this->check_xcloner_tmp_path(1))
			return false;
			
		return true;	
	}
	
	public function get_constant($var)
	{
		return $this->$var;
	}
		
	public function check_min_php_version($return_bool = 0)
	{
		
		if($return_bool == 1)
		{
			if(version_compare(phpversion(), $this->min_php_version, '<'))
				return false;
			else
				return true;
		}
		
		return phpversion();
	}
	
	public function check_safe_mode($return_bool=0)
	{
		$safe_mode = "Off";
		
		if($return_bool)
		{
			if( ini_get('safe_mode') )
				return false;
			else
				return true;
		}
		
		if( ini_get('safe_mode') )
			$safe_mode = "On";
			
		return $safe_mode;
	}
	
	public function check_xcloner_start_path($return_bool=0)
	{
		$path = $this->xcloner_settings->get_xcloner_start_path();
		
		if($return_bool)
		{
			if(!file_exists($path))
				return false;
				
			return is_readable($path);
		}
		
		return $path;
	}
	
	public function check_xcloner_tmp_path($return_bool=0)
	{
		$path = $this->xcloner_settings->get_xcloner_tmp_path();
		
		if($return_bool)
		{
			if(!file_exists($path))
				return false;
			
			if(!is_writeable($path))	
				@chmod($path, 0777);
					
			return is_writeable($path);
		}
		
		return $path;
	}
	
	public function check_xcloner_store_path($return_bool=0)
	{
		$path = $this->xcloner_settings->get_xcloner_store_path();
		
		if($return_bool)
		{
			if(!file_exists($path))
				return false;
			
			if(!is_writeable($path))	
				@chmod($path, 0777);
				
			return is_writeable($path);
		}
		
		return $path;
	}
	
	public function get_max_execution_time()
	{
		return ini_get('max_execution_time');
	}
	
	public function get_memory_limit()
	{
		return ini_get('memory_limit');
	}
	
	public function get_open_basedir()
	{
		$open_basedir =  ini_get('open_basedir');
		
		if(!$open_basedir)
			$open_basedir = "none";
		return $open_basedir;	
	}
	
	public function get_free_disk_space()
	{
			return $this->file_format_size(disk_free_space($this->xcloner_settings->get_xcloner_store_path()));
	}
	
	public function file_format_size($bytes, $decimals = 2) {
	  $unit_list = array('B', 'KB', 'MB', 'GB', 'PB');
	
	  if ($bytes == 0) {
	    return $bytes . ' ' . $unit_list[0];
	  }
	
	  $unit_count = count($unit_list);
	  for ($i = $unit_count - 1; $i >= 0; $i--) {
	    $power = $i * 10;
	    if (($bytes >> $power) >= 1)
	      return round($bytes / (1 << $power), $decimals) . ' ' . $unit_list[$i];
	  }
	}
}
?>
