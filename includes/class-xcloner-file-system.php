<?php
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
use League\Flysystem\Adapter\Local;

class Xcloner_File_System{
	
	protected $excluded_files 			= "";
	protected $excluded_files_handler 	= "perm.txt";
	protected $temp_dir_handler 		= ".dir";
	protected $filesystem;
	protected $tmp_filesystem;
	protected $storage_filesystem;
	protected $storage_filesystem_write;
	protected $xcloner_settings_append;
	
	protected $files_counter;
	
	public function __construct()
	{
		$this->xcloner_settings 		= new Xcloner_Settings();
		$adapter = new Local($this->xcloner_settings->get_xcloner_start_path(),LOCK_EX, 'SKIP_LINKS');
		$this->filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));
					
		$adapter = new Local($this->xcloner_settings->get_xcloner_tmp_path(),LOCK_EX, 'SKIP_LINKS');
		$this->tmp_filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));
		$adapter = new Local($this->xcloner_settings->get_xcloner_tmp_path(),LOCK_EX|FILE_APPEND, 'SKIP_LINKS');
		$this->tmp_filesystem_append = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));

		$adapter = new Local($this->xcloner_settings->get_xcloner_store_path(),LOCK_EX, 'SKIP_LINKS');
		$this->storage_filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));	
		
		$adapter = new Local($this->xcloner_settings->get_xcloner_store_path(),FILE_APPEND, 'SKIP_LINKS');
		$this->storage_filesystem_append = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));	
	}
	
	public function start_file_recursion($init = 0)
	{
		if($init)
		{
			$this->debug(sprintf(__("Starting the filesystem scanner on root folder %s"), $this->xcloner_settings->get_xcloner_start_path()));
			$this->do_system_init();
		}
		
		if($this->storage_filesystem->has($this->temp_dir_handler)){
		//.dir exists, we presume we have files to iterate	
			$content = $this->storage_filesystem->read($this->temp_dir_handler);
			$files = array_filter(explode("\n", $content));
			$this->storage_filesystem->delete($this->temp_dir_handler);
			
			foreach($files as $file)
				$this->build_files_list($file);
		}else{
			$this->build_files_list();
		}
		
		if($this->scan_finished())
			return false;
			
		return true;	
	}
	
	private function do_system_init()
	{
		$this->files_counter = 0;
			
		$this->storage_filesystem->write("index.html","");
		
		if($this->storage_filesystem->has($this->excluded_files_handler))
			$this->storage_filesystem->delete($this->excluded_files_handler);
		
		if($this->storage_filesystem->has($this->temp_dir_handler))	
			$this->storage_filesystem->delete($this->temp_dir_handler);
	}
	
	public function get_scanned_files_num()
	{
		return $this->files_counter;
	}
	
	public function build_files_list($folder = "")
	{
		//if we start with the root folder(empty value), we initializa the file system
		if(!$folder){
			
		}
			
		try{
			
			$files = $this->filesystem->listContents($folder);
			foreach($files as $file)
			{
				if(!$matching_pattern = $this->is_excluded($file)){
					$this->debug(sprintf(__("Adding %s to the filesystem list"), $file['path']));
					$file['visibility'] = $this->filesystem->getVisibility($file['path']);
					$this->store_file($file);
					$this->files_counter++;
					
				}else{
					$this->debug(sprintf(__("Excluding %s from the filesystem list, matching pattern %s"), $file['path'], $matching_pattern));
					}
			}
			
		}catch(Exception $e){
			
			$this->debug("E_ERROR", $e->getMessage());
		
		}
		
			
	}
	
	public function is_excluded($file)
	{
		foreach($this->excluded_files as $excluded_file_pattern)
		{
			if(strstr("$".$file['path'], "$".$excluded_file_pattern))
				return $excluded_file_pattern;
		}
		
		return false;
	}
	
	public function list_directory($path)
	{
		return $this->filesystem->listContents($path);
	}
	
	
	public function store_file($file)
	{
		if(!isset($file['size']))
			$file['size'] = 0;
		$line = $file['path']."|".$file['timestamp']."|".$file['size']."|".$file['visibility']."\n";

		$this->storage_filesystem_append->write($this->excluded_files_handler, $line);
		
		if($file['type'] == "dir"){
				$this->storage_filesystem_append->write($this->temp_dir_handler, $file['path']."\n");
		}
	}
	
	public function set_excluded_files($excluded_files)
	{
		$this->excluded_files = $excluded_files;
		return $this;
	}
	
	public function get_fileystem_handler()
	{
		return $this->filesystem;
	}
	
	private function scan_finished()
	{
		if($this->storage_filesystem_append->has($this->temp_dir_handler) && $this->storage_filesystem_append->getSize($this->temp_dir_handler))
			return false;
		
		if($this->storage_filesystem->has($this->temp_dir_handler))
			$this->storage_filesystem->delete($this->temp_dir_handler);
		
		return true;
	}
	
	/*
	 * var $type = (E_ERROR, E_WARNING, E_NOTICE)
	 */ 
	protected function debug( $message, $type = 'E_NOTICE')
	{
		//echo sprintf("%s - %s: %s \n", date("Y-m-d H:i:s"), $type, $message);
	}
	
}
