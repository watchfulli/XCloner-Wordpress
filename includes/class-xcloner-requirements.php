<?php

class XCloner_Requirements
{
	
	var $min_php_version 	= "5.3.0";
	var $safe_mode			= "Off";
	
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
		$path = Xcloner_Settings::get_xcloner_start_path();
		
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
		$path = Xcloner_Settings::get_xcloner_tmp_path();
		
		if($return_bool)
		{
			if(!file_exists($path))
				return false;
				
			return is_writeable($path);
		}
		
		return $path;
	}
	
	public function check_xcloner_store_path($return_bool=0)
	{
		$path = Xcloner_Settings::get_xcloner_store_path();
		
		if($return_bool)
		{
			if(!file_exists($path))
				return false;
				
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
	
	public function estimate_read_write_time()
	{
		$tmp_path = Xcloner_Settings::get_xcloner_tmp_path();
		$tmp_file = tempnam(sys_get_temp_dir(), 'prefix');
		
		$start_time = microtime();
		$data = str_repeat(rand(0,9), 1024*1024);
		file_put_contents($tmp_file, $data);
		
		$fp = fopen($tmp_file, "w");
		for($i=0;$i<(1024*1024);$i+=512)
		{
			fwrite($fp, str_repeat(rand(0,9), 512));
		}
		fclose($fp);
		
		$end_time = microtime() - $start_time;

		
		$return['writing_time'] = $end_time;
		
		$return['reading_time']	= $this->estimate_reading_time($tmp_file);
		
		unlink($tmp_file);
		
		return $return;
	}
	
	public function estimate_reading_time($tmp_file)
	{
		$start_time = microtime();
		$fp = fopen($tmp_file, "r");
		while(!feof($fp))
		{
			fread($fp, 512);
		}
		fclose($fp);
		$end_time = microtime() - $start_time;
		
		return $end_time;
	}
	
	public function get_free_disk_space()
	{
			return $this->file_format_size(disk_free_space(Xcloner_Settings::get_xcloner_store_path()));
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
