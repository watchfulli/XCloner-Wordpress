<?php
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
use League\Flysystem\Adapter\Local;

class Xcloner_File_System{
	
	private $excluded_files 			= "";
	private $additional_regex_patterns	= array();
	private $excluded_files_by_default	= array("administrator/backups", "wp-content/backups");
	private $included_files_handler 	= "backup_files.csv";
	private $temp_dir_handler 			= ".dir";
	public  $filesystem;
	public  $tmp_filesystem;
	public  $storage_filesystem;
	private $xcloner_settings_append;
	private $xcloner_container;
	private $diff_timestamp_start		= "";
	
	private $logger;
	private $start_adapter;
	private $tmp_adapter;
	private $storage_adapter;
	private $xcloner_requirements;
	private $xcloner_settings;
	private $start_filesystem;
	private $tmp_filesystem_append;
	private $storage_filesystem_append;
	
	private $files_counter;
	private $files_size;
	private $last_logged_file;
	private $folders_to_process_per_session = 25;
	private $backup_archive_extensions = array("tar", "tgz", "tar.gz", "gz", "csv");
	private $backup_name_tags = array('[time]', '[hostname]', '[domain]');
	
	public function __construct(Xcloner $xcloner_container, $hash = "")
	{
		$this->xcloner_container = $xcloner_container;
		
		$this->logger 				= $xcloner_container->get_xcloner_logger()->withName("xcloner_file_system");
		$this->xcloner_settings 	= $xcloner_container->get_xcloner_settings();

		try{
			
			$this->start_adapter = new Local($this->xcloner_settings->get_xcloner_start_path(),LOCK_EX, 'SKIP_LINKS');
			$this->start_filesystem = new Filesystem($this->start_adapter, new Config([
					'disable_asserts' => true,
				]));
			
			$this->tmp_adapter = new Local($this->xcloner_settings->get_xcloner_tmp_path(),LOCK_EX, 'SKIP_LINKS');
			$this->tmp_filesystem = new Filesystem($this->tmp_adapter, new Config([
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
			
			$this->storage_adapter = new Local($this->xcloner_settings->get_xcloner_store_path(),FILE_APPEND, 'SKIP_LINKS');
			$this->storage_filesystem_append = new Filesystem($this->storage_adapter, new Config([
					'disable_asserts' => true,
				]));
		}catch(Exception $e){
			$this->logger->error("Filesystem Initialization Error: ".$e->getMessage());
		}
		
		
		if($value = get_option('xcloner_directories_to_scan_per_request'))
			$this->folders_to_process_per_session = $value;

	}
	
	public function set_diff_timestamp_start($timestamp = "")
	{
		if($timestamp)
		{
			$this->logger->info(sprintf("Setting Differential Timestamp To %s", date("Y-m-d", $timestamp)), array("FILESYSTEM", "DIFF"));
			$this->diff_timestamp_start = $timestamp;
		}
	}
	
	public function get_diff_timestamp_start()
	{
		return $this->diff_timestamp_start;
	}

	private function get_xcloner_container()
	{
		return $this->xcloner_container;
	}
	
	
	public function set_hash($hash)
	{
		$this->xcloner_settings->set_hash($hash);
	}
	
	public function get_hash($hash)
	{
		$this->xcloner_settings->get_hash();
	}
	
	public function get_tmp_filesystem()
	{
		return $this->tmp_filesystem;
	}
	
	public function get_storage_filesystem($remote_storage_selection = "")
	{
		if($remote_storage_selection != "")
		{
			$remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
			$method = "get_".$remote_storage_selection."_filesystem";
			
			if(!method_exists($remote_storage, $method))
				return false;
				
			list($adapter, $filesystem) = $remote_storage->$method();	
			
			return $filesystem;
		}

		return $this->storage_filesystem;
	}
	
	public function get_tmp_filesystem_adapter()
	{
		return $this->tmp_adapter;
	}
	
	public function get_tmp_filesystem_append()
	{
		return $this->tmp_filesystem_append;
	}
	
	public function get_start_adapter()
	{
		return $this->start_adapter;
	}
	
	public function get_start_filesystem()
	{
		return $this->start_filesystem;
	}
	
	public function get_logger()
	{
		return $this->logger;
	}
	
	public function get_start_path_file_info($file)
	{
		$info= $this->getMetadataFull('start_adapter', $file);
		return $this->start_filesystem->normalizeFileInfo($info);
	}
	
	public function get_storage_path_file_info($file)
	{
		return $this->getMetadataFull('storage_adapter', $file);
	}
	
	public function get_included_files_handler($metadata  = 0)
	{
		$path = $this->included_files_handler;
		if(!$metadata)
			return $path;
		
		$spl_info = $this->getMetadataFull('tmp_adapter', $path);
		return $spl_info;
		
	}
		
	public function get_temp_dir_handler()
	{
		return $this->temp_dir_handler;
	}
	
	public function get_latest_backup()
	{
		$files = $this->get_backup_archives_list();
		
		if(is_array($files))
			$this->sort_by($files, "timestamp","desc");
		
		$new_list = array();
		
		foreach($files as $key=>$file)
			if(!isset($file['parent']))
				$new_list[] = ($files[$key]);

		if(isset($new_list[0]))
			return $new_list[0];
	}
	
	public function get_latest_backups()
	{
		$files = $this->get_backup_archives_list();

		if(is_array($files))
			$this->sort_by($files, "timestamp","desc");
		
		$new_list = array();
		
		foreach($files as $key=>$file)
			if(!isset($file['parent']))
				$new_list[] = ($files[$key]);

		return $new_list;
	}
	
	public function get_storage_usage()
	{
		$files = $this->get_backup_archives_list();
		$total = 0;
		
		if(is_array($files))
			foreach($files as $file)
				$total += $file['size'];
				
		return $total;		
	}
	
	public function is_part($backup_name)
	{
		if(stristr($backup_name, "-part"))
			return true;
		
		return false;	
	}
	
	public function is_multipart($backup_name)
	{
		if(stristr($backup_name, "-multipart"))
			return true;
		
		return false;	
	}
	
	public function get_backup_size($backup_name)
	{
		$backup_size = $this->get_storage_filesystem()->getSize($backup_name);
		if($this->is_multipart($backup_name))
		{
			$backup_parts = $this->get_multipart_files($backup_name);
			foreach($backup_parts as $part_file)
				$backup_size += $this->get_storage_filesystem()->getSize($part_file);
		}
		
		return $backup_size;
	}
	
	public function get_multipart_files($backup_name, $storage_selection = "")
	{
		$files = array();
		
		if($this->is_multipart($backup_name))
		{
			$lines = explode(PHP_EOL, $this->get_storage_filesystem($storage_selection)->read($backup_name));
			foreach($lines as $line)
			{
				if($line)
				{
					$data = str_getcsv($line);
					$files[] = $data[0];
				}
			}
		}
		
		return $files;
	}
	
	public function delete_backup_by_name($backup_name, $storage_selection = "")
	{
		if($this->is_multipart($backup_name))
		{
			$lines = explode(PHP_EOL, $this->get_storage_filesystem($storage_selection)->read($backup_name));
			foreach($lines as $line)
			{
				if($line)
				{
					$data = str_getcsv($line);
					$this->get_storage_filesystem($storage_selection)->delete($data[0]);
				}
			}
		}
		
		if($this->get_storage_filesystem($storage_selection)->delete($backup_name))
			$return = true;
		else
			$return = false;
			
		return $return;	
	}
	
	public function getMetadataFull($adapter = "storage_adapter" , $path)
    {
        $location = $this->$adapter->applyPathPrefix($path);
        $spl_info = new SplFileInfo($location);
        
        return ($spl_info);
    }
	
	
	public function get_backup_archives_list($storage_selection = "")
	{
		$list = array();
		

		if(method_exists($this->get_storage_filesystem($storage_selection), "listContents"))
			$list = $this->get_storage_filesystem($storage_selection)->listContents();

		
		$backup_files = array();
		$parents = array();
		
		foreach($list as $file_info)
		{
			if(isset($file_info['extension']) and $file_info['extension'] == "csv")
			{
				$data = array();
				
				$lines = explode(PHP_EOL, $this->get_storage_filesystem($storage_selection)->read($file_info['path']));
				foreach($lines as $line)
					if($line)
					{
						$data = str_getcsv($line);
						if(is_array($data)){
							$parents[$data[0]] = $file_info['basename'];
							$file_info['childs'][] = $data;
							$file_info['size'] += $data[2];
						}
					}
						
			}
			
			if($file_info['type'] == 'file' and isset($file_info['extension']) and in_array($file_info['extension'], $this->backup_archive_extensions))
				$backup_files[$file_info['path']] = $file_info;
		}
		
		foreach($backup_files as $key=>$file_info)
		{
			if(!isset($backup_files[$key]['timestamp']))
			{
				//$backup_files[$key]['timestamp'] = $this->get_storage_filesystem($storage_selection)->getTimestamp($file_info['path']);
			}
			
			if(isset($parents[$file_info['basename']]))
				$backup_files[$key]['parent'] = $parents[$file_info['basename']];
		}
		
		return $backup_files;
	}
	
	public function start_file_recursion($init = 0)
	{
		if($init)
		{
			$this->logger->info(sprintf(__("Starting the filesystem scanner on root folder %s"), $this->xcloner_settings->get_xcloner_start_path()));
			$this->do_system_init();
		}
		
		if($this->tmp_filesystem->has($this->get_temp_dir_handler())){
		//.dir exists, we presume we have files to iterate	
			$content = $this->tmp_filesystem->read($this->get_temp_dir_handler());
			$files = array_filter(explode("\n", $content));
			$this->tmp_filesystem->delete($this->get_temp_dir_handler());
			
			$counter = 0;
			foreach($files as $file)
			{
				if($counter < $this->folders_to_process_per_session){
					$this->build_files_list($file);
					$counter++;
				}else{
					$this->tmp_filesystem_append->write($this->get_temp_dir_handler(), $file."\n");
				}
			}
		}else{
			$this->build_files_list();
		}
		
		if($this->scan_finished())
		{
			$metadata_dumpfile = $this->get_tmp_filesystem()->getMetadata("index.html");
			$this->store_file($metadata_dumpfile, 'tmp_filesystem');
			$this->files_counter++;
		
			//adding included dump file to the included files list
			if($this->get_tmp_filesystem()->has($this->get_included_files_handler()))
			{
				$metadata_dumpfile = $this->get_tmp_filesystem()->getMetadata($this->get_included_files_handler());
				$this->store_file($metadata_dumpfile, 'tmp_filesystem');
				$this->files_counter++;
			}
		
			//adding a default index.html to the temp xcloner folder
			if(!$this->get_tmp_filesystem()->has("index.html"))
			{
				$this->get_tmp_filesystem()->write("index.html","");
			}
			
			//adding the default log file
			if($this->get_tmp_filesystem()->has($this->xcloner_settings->get_logger_filename(1)))
			{
				$metadata_dumpfile = $this->get_tmp_filesystem()->getMetadata($this->xcloner_settings->get_logger_filename(1));
				$this->store_file($metadata_dumpfile, 'tmp_filesystem');
				$this->files_counter++;
			}
		
			return false;
		}	
	
		return true;	
	}
	
	public function get_backup_attachments()
	{
		$return = array();
		
		$files_list_file = $this->xcloner_settings->get_xcloner_tmp_path().DS.$this->get_included_files_handler();
		if(file_exists($files_list_file))
			{
				$return[] = $files_list_file;
			}
			
		if($this->xcloner_settings->get_xcloner_option('xcloner_enable_log'))
		{
			$log_file = $this->xcloner_settings->get_xcloner_tmp_path().DS.$this->xcloner_settings->get_logger_filename(1);
			if(!file_exists($log_file))
			{
				$log_file = $this->xcloner_settings->get_xcloner_store_path().DS.$this->xcloner_settings->get_logger_filename();
			}
			
			if(file_exists($log_file))
			{
				$return[] = $log_file;
			}
		}
		
		return $return;
	}
	
	public function remove_tmp_filesystem()
	{
		//delete the temporary folder
		$this->logger->info(sprintf("Deleting the temporary storage folder %s", $this->xcloner_settings->get_xcloner_tmp_path()));
		
		$contents = $this->get_tmp_filesystem()->listContents();
	
		if(is_array($contents))
		foreach($contents as $file_info)
			$this->get_tmp_filesystem()->delete($file_info['path']);
			
		@rmdir($this->xcloner_settings->get_xcloner_tmp_path());
		
		return;
	}
	
	public function cleanup_tmp_directories()
	{
		$adapter = new Local($this->xcloner_settings->get_xcloner_tmp_path(false),LOCK_EX|FILE_APPEND, 'SKIP_LINKS');
		$tmp_filesystem = new Filesystem($adapter, new Config([
					'disable_asserts' => true,
				]));
				
		$contents = $tmp_filesystem->listContents();
		
		foreach($contents as $file)
		{
			if(preg_match("/.xcloner-(.*)/",$file['path']))
			{
				$tmp_filesystem->deleteDir($file['path']);
				$this->logger->info(sprintf("Delete temporary directory %s", $file['path']));
			}
		}
		
		return true;
	}
	
	private function do_system_init()
	{
		$this->files_counter = 0;
		
		if(!$this->storage_filesystem->has("index.html"))	
			$this->storage_filesystem->write("index.html","");
		
		if(!$this->tmp_filesystem->has("index.html"))	
			$this->tmp_filesystem->write("index.html","");
			
		if($this->tmp_filesystem->has($this->get_included_files_handler()))
			$this->tmp_filesystem->delete($this->get_included_files_handler());
		
		if($this->tmp_filesystem->has($this->get_temp_dir_handler()))	
			$this->tmp_filesystem->delete($this->get_temp_dir_handler());
	}
	
	public function get_scanned_files_num()
	{
		return $this->files_counter;
	}
	
	public function get_scanned_files_total_size()
	{
		return $this->files_size;
	}
	
	public function last_logged_file()
	{
		return $this->last_logged_file;
	}
	
	public static function is_regex($regex) {
		return preg_match("/^\^(.*)\$$/i",$regex);
	}
	
	public function set_excluded_files($excluded_files = array())
	{
		if(!is_array($excluded_files))
		{
			$excluded_files = array();
		}
		
		foreach($excluded_files as $excl)
		{
			
			if($this->is_regex($excl))
			{
				$this->additional_regex_patterns[] = $excl;
			}
		}
		
		$this->excluded_files = array_merge($excluded_files, $this->excluded_files_by_default);
		
		return $this->excluded_files;
	}
	
	public function get_excluded_files()
	{
		return $this->excluded_files_by_default;
	}
	
	public function list_directory($path)
	{
		return $this->start_filesystem->listContents($path);
	}
	
	public function build_files_list($folder = "")
	{
		$this->logger->debug(sprintf(("Building the files system list")));
		
		//if we start with the root folder(empty value), we initializa the file system
		if(!$folder){
			
		}
			
		try{
			
			$files = $this->start_filesystem->listContents($folder);
			foreach($files as $file)
			{
				if(!is_readable($this->xcloner_settings->get_xcloner_start_path().DS.$file['path']))
				{
					$this->logger->info(sprintf(__("Excluding %s from the filesystem list, file not readable"), $file['path']), array("FILESYSTEM SCAN","NOT READABLE"));
				}
				elseif(!$matching_pattern = $this->is_excluded($file) ){
					$this->logger->info(sprintf(__("Adding %s to the filesystem list"), $file['path']), array("FILESYSTEM SCAN","INCLUDE"));
					$file['visibility'] = $this->start_filesystem->getVisibility($file['path']);
					if($this->store_file($file))
					{
						$this->files_counter++;
					}
					if(isset($file['size']))
						$this->files_size += $file['size'];
					
				}else{
					$this->logger->info(sprintf(__("Excluding %s from the filesystem list, matching pattern %s"), $file['path'], $matching_pattern), array("FILESYSTEM SCAN","EXCLUDE"));
					}
			}
			
		}catch(Exception $e){
			
			$this->logger->error($e->getMessage());
		
		}
			
	}
	
	public function estimate_read_write_time()
	{
		$tmp_file = ".xcloner".substr(md5(time()), 0, 5);
				
		$start_time = microtime();
		
		$data = str_repeat(rand(0,9), 1024*1024); //write 1MB data
		
		try{
			$this->tmp_filesystem->write($tmp_file, $data);
			
			$end_time = microtime() - $start_time;
		
			$return['writing_time'] = $end_time;
		
			$return['reading_time']	= $this->estimate_reading_time($tmp_file);
		
			$this->tmp_filesystem->delete($tmp_file);
		
		}catch(Exception $e){
			
			$this->logger->error($e->getMessage());
			
		}
		
		return $return;
	}
	
	public function backup_storage_cleanup()
	{
		$this->logger->info(sprintf(("Cleaning the backup storage on matching rules")));
		
		$_storage_size = 0;
		$_backup_files_list = array();
		
		//rule date limit
		$current_timestamp = strtotime("-".$this->xcloner_settings->get_xcloner_option('xcloner_cleanup_retention_limit_days')." days");
		
		$files = $this->storage_filesystem->listContents();
		
		if(is_array($files))
			foreach($files as $file)
			{
				if(isset($file['extension']) and in_array($file['extension'], $this->backup_archive_extensions))
				{
					$_storage_size += $file['size']; //bytes
					$_backup_files_list[] = $file;
				}
			}
		
		
		$this->sort_by($_backup_files_list, "timestamp","asc");
		
		$_backups_counter = sizeof($_backup_files_list);
				
		foreach($_backup_files_list as $file)
		{
			//processing rule folder capacity
			if($this->xcloner_settings->get_xcloner_option('xcloner_cleanup_capacity_limit') &&
			$_storage_size >= ($set_storage_limit = 1024*1024*$this->xcloner_settings->get_xcloner_option('xcloner_cleanup_capacity_limit')))	//bytes	
			{
				$this->storage_filesystem->delete($file['path']);
				$_storage_size -= $file['size'];
				$this->logger->info("Deleting backup ".$file['path']." matching rule", array("STORAGE SIZE LIMIT", $_storage_size." >= ".$set_storage_limit));
			}
			
			//processing rule days limit
			if($this->xcloner_settings->get_xcloner_option('xcloner_cleanup_retention_limit_days') && $current_timestamp >= $file['timestamp'])
			{
				$this->storage_filesystem->delete($file['path']);
				$this->logger->info("Deleting backup ".$file['path']." matching rule", array("RETENTION LIMIT TIMESTAMP", $file['timestamp']." =< ".$this->xcloner_settings->get_xcloner_option('xcloner_cleanup_retention_limit_days')));
			}
			
			//processing backup countert limit
			if($this->xcloner_settings->get_xcloner_option('xcloner_cleanup_retention_limit_archives') && $_backups_counter >= $this->xcloner_settings->get_xcloner_option('xcloner_cleanup_retention_limit_archives'))
			{
				$this->storage_filesystem->delete($file['path']);
				$_backups_counter--;
				$this->logger->info("Deleting backup ".$file['path']." matching rule", array("BACKUP QUANTITY LIMIT", $_backups_counter." >= ".$this->xcloner_settings->get_xcloner_option('xcloner_cleanup_retention_limit_archives')));
			}
			
				
		}
		
	}
	
	public function estimate_reading_time($tmp_file)
	{
		$this->logger->debug(sprintf(("Estimating file system reading time")));
		
		$start_time = microtime();
		
		$this->tmp_filesystem->read($tmp_file);
		
		$end_time = microtime() - $start_time;
		
		return $end_time;
	
	}
	
	public function process_backup_name($name = "", $max_length=100)
	{
		if(!$name)
			$name = $this->xcloner_settings->get_default_backup_name();
		
		foreach($this->backup_name_tags as $tag)
		{
			if($tag == '[time]')
				$name = str_replace($tag, date("Y-m-d_H-i"),$name);
			elseif($tag == '[hostname]')
				$name = str_replace($tag, gethostname() ,$name);	
			elseif($tag == '[domain]')
			{
				$domain = parse_url(admin_url(), PHP_URL_HOST);
				$name = str_replace($tag, $domain ,$name);	
			}
		}
		
		if($max_length)
			$name = substr($name, 0, $max_length);
			
		return $name;	
	}
	
	public function sort_by( &$array, $field, $direction = 'asc')
	{
		if(strtolower($direction) == "desc" || $direction == SORT_DESC)
			$direction = SORT_DESC;
		else
			$direction = SORT_ASC;
					
	   $array = $this->array_orderby($array, $field, $direction);
	    
	   return true;
	}
	
	private function array_orderby()
	{
	    $args = func_get_args();
	    $data = array_shift($args);
	    
	    foreach ($args as $n => $field) {
	        if (is_string($field)) {
	            $tmp = array();
	            foreach ($data as $key => $row)
	            {
					if(is_array($row))
						$tmp[$key] = $row[$field];
					else
						$tmp[$key] = $row->$field;
				}
	            $args[$n] = $tmp;
	            }
	    }
	    $args[] = &$data;
	    
	    call_user_func_array('array_multisort', $args);
	    
	    return array_pop($args);
	}
	
	private function check_file_diff_time($file)
	{
		if($this->get_diff_timestamp_start() != "")
		{
			$fileMeta = $this->getMetadataFull("start_adapter", $file['path']);
			$timestamp = $fileMeta->getMTime();
			if($timestamp < $fileMeta->getCTime())
			{
				$timestamp = $fileMeta->getCTime();
			}
			
			if($timestamp <= $this->get_diff_timestamp_start())
			{
				return  " file DIFF timestamp ".$timestamp." < ". $this->diff_timestamp_start;
			}
		}
		
		return false;
	}
	
	public function is_excluded($file)
	{
		$this->logger->debug(sprintf(("Checking if %s is excluded"), $file['path']));
		
		if($xcloner_exclude_files_larger_than_mb = $this->xcloner_settings->get_xcloner_option('xcloner_exclude_files_larger_than_mb'))
		{
			if(isset($file['size']) && $file['size'] > $this->calc_to_bytes($xcloner_exclude_files_larger_than_mb))
				return "> ".$xcloner_exclude_files_larger_than_mb."MB";
		}
		
		if(!sizeof($this->excluded_files))
		{
			$this->set_excluded_files();
		}
						
		if(is_array($this->excluded_files))
		foreach($this->excluded_files as $excluded_file_pattern)
		{
			if($excluded_file_pattern == "/")
				$needle = "$";
			else
				$needle = "$".$excluded_file_pattern;
				
			if(strstr("$".$file['path'], $needle)){
				return $excluded_file_pattern;
			}
		}
		
		if( $regex = $this->is_excluded_regex($file))
			return $regex;
		
		if($file['type'] == "file")
		{
			$check_file_diff_timestamp = $this->check_file_diff_time($file);
			if($check_file_diff_timestamp)
			{
				return $check_file_diff_timestamp;
			}
		}
		
		return false;
	}
	
	/*REGEX examples
	 * 
	* exclude all except .php file
	* PATTERN: ^(.*)\.(.+)$(?<!(php))
	* 
	* exclude all except .php and .txt
	* PATTERN: ^(.*)\.(.+)$(?<!(php|txt))";
	* 
	* exclude all .svn and .git
	* PATTERN: ^(.*)\.(svn|git)(.*)$";
	* 
	* exclude root directory /test
	* PATTERN: "^\/test(.*)$";
	* 
	* exclude the wp-admin folder
	* PATTERN: ^(\/wp-admin)(.*)$";
	* 
	* exclude the wp-admin, wp-includes and wp-config.php
	* PATTERN: ^\/(wp-admin|wp-includes|wp-config.php)(.*)$";
	* 
	* exclude all .avi files
	* PATTERN: ^(.*)$(?<=(avi))";
	* 
	* exclude all .jpg and gif files
	* PATTERN: ^(.*)$(?<=(gif|jpg))";
	* 
	* exclude all cache folders from wp-content/
	* PATTERN: ^\/wp-content(.*)\/cache($|\/)(.*)";
	* 
	* exclude the backup folders
	* PATTERN: (^|^\/)(wp-content\/backups|administrator\/backups)(.*)$";
	*/
	private function is_excluded_regex($file)
	{
		//$this->logger->debug(sprintf(("Checking if %s is excluded"), $file['path']));
		
		$regex_patterns = explode(PHP_EOL, $this->xcloner_settings->get_xcloner_option('xcloner_regex_exclude'));
		
		if(is_array($this->additional_regex_patterns))
		{
			$regex_patterns = array_merge($regex_patterns, $this->additional_regex_patterns);
		}
		
		//print_r($regex_patterns);exit;
		
		if(is_array($regex_patterns))
		{
			//$this->excluded_files = array();
			//$this->excluded_files[] ="(.*)\.(git)(.*)$";
			//$this->excluded_files[] ="wp-content\/backups(.*)$";
			
			foreach($regex_patterns as $excluded_file_pattern)
			{
				
				if( substr($excluded_file_pattern, strlen($excluded_file_pattern)-1, strlen($excluded_file_pattern)) == "\r")
					$excluded_file_pattern = substr($excluded_file_pattern, 0, strlen($excluded_file_pattern)-1);
					
				if($file['path'] == "/")
					$needle = "/";
				else
					$needle = "/".$file['path'];
				//echo $needle."---".$excluded_file_pattern."---\n";
				
				if(@preg_match("/(^|^\/)".$excluded_file_pattern."/i", $needle)){
					return $excluded_file_pattern;
				}
			}
		}
		
		return false;
	}
	
	public function store_file($file, $storage = 'start_filesystem')
	{
		$this->logger->debug(sprintf("Storing %s in the backup list", $file['path']));
		
		if(!isset($file['size']))
			$file['size'] = 0;
		if(!isset($file['visibility']))	
			$file['visibility'] = "private";
		
		$csv_filename = str_replace('"','""', $file['path']);
		
		$line = '"'.($csv_filename).'","'.$file['timestamp'].'","'.$file['size'].'","'.$file['visibility'].'","'.$storage.'"'.PHP_EOL;
		
		$this->last_logged_file = $file['path'];
		
		if($file['type'] == "dir"){
			try{
				$this->tmp_filesystem_append->write($this->get_temp_dir_handler(), $file['path']."\n");
			}catch(Exception $e){
				$this->logger->error($e->getMessage());	
			}
		}
		
		if($this->get_diff_timestamp_start())
		{
			if($file['type'] != "file" && $response = $this->check_file_diff_time($file))
			{
				$this->logger->info(sprintf("Directory %s archiving skipped on differential backup %s", $file['path'], $response), array("FILESYSTEM SCAN","DIR DIFF"));
				return false;
			}
		}
		
		try{
			if(!$this->tmp_filesystem_append->has($this->get_included_files_handler()))
			{
				//adding fix for UTF-8 CSV preview
				$start_line = "\xEF\xBB\xBF".'"Filename","Timestamp","Size","Visibility","Storage"'.PHP_EOL;
				$this->tmp_filesystem_append->write($this->get_included_files_handler(), $start_line);
			}
			
			$this->tmp_filesystem_append->write($this->get_included_files_handler(), $line);
		
		}catch(Exception $e){
		
			$this->logger->error($e->getMessage());	
		}
		
		return true;
	}
	
	public function get_fileystem_handler()
	{
		return $this;
	}
	
	public function get_filesystem($system = "")
	{
		if($system == "storage_filesystem_append")
			return $this->storage_filesystem_append;
		elseif($system == "tmp_filesystem_append")
			return $this->tmp_filesystem_append;
		elseif($system == "tmp_filesystem")
			return $this->tmp_filesystem;
		elseif($system == "storage_filesystem")
			return $this->storage_filesystem;
		else
			return $this->start_filesystem;	
	}
	
	public function get_adapter($system)
	{
		if($system == "tmp_filesystem")
			return $this->tmp_adapter;
		elseif($system == "storage_filesystem")
			return $this->storage_adapter;
		else
			return $this->start_adapter;	
	}
	
	private function scan_finished()
	{
		if($this->tmp_filesystem_append->has($this->get_temp_dir_handler()) && $this->tmp_filesystem_append->getSize($this->get_temp_dir_handler()))
			return false;
		
		if($this->tmp_filesystem->has($this->get_temp_dir_handler()))
			$this->tmp_filesystem->delete($this->get_temp_dir_handler());
		
		$this->logger->debug(sprintf(("File scan finished")));
			
		return true;
	}
	
	private function calc_to_bytes($mb_size)
	{
		return $mb_size*(1024*1024);
	}
	
}
