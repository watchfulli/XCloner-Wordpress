<?php

if(!defined('AUTH_KEY'))
{
	define('AUTH_KEY', '');
}

if(!defined("DS"))
{
	define("DS", DIRECTORY_SEPARATOR);
}

if(!defined('XCLONER_PLUGIN_ACCESS') || XCLONER_PLUGIN_ACCESS != 1)
{	
	if(!AUTH_KEY)
	{
			Xcloner_Restore::send_response("404", "Could not run restore script, AUTH_KEY not set!");
			exit;
	}
	
	if(!isset($_REQUEST['hash']))
	{
			Xcloner_Restore::send_response("404", "Could not run restore script, sent HASH is empty!");
			exit;
	}
	
	if($_REQUEST['hash'] != AUTH_KEY)
	{
			Xcloner_Restore::send_response("404", "Could not run restore script, AUTH_KEY doesn't match the sent HASH!");
			exit;
	}
}

//check minimum PHP version
if(version_compare(phpversion(), Xcloner_Restore::xcloner_minimum_version, '<'))
{
	Xcloner_Restore::send_response(500, sprintf(("XCloner requires minimum PHP version %s in order to run correctly. We have detected your version as %s"),Xcloner_Restore::xcloner_minimum_version, phpversion()) );
	exit;

}

$file = dirname( __DIR__ )  . DS.'vendor'.DS.'autoload.php';

if(file_exists($file))
{
	
	require_once($file);
}
elseif(file_exists("vendor.phar") and extension_loaded('phar'))
{
	require_once(__DIR__.DS."vendor.phar");
}else{	
	
	$file = dirname( __FILE__ )  . DS.'vendor'.DS.'autoload.php';
	
	if(!file_exists($file))
	{
		Xcloner_Restore::send_response("404", "File $file does not exists, please extract the vendor.tgz archive on the server or enable PHP Phar module!");
		exit;
	}
	
	require_once($file);
}


use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
use League\Flysystem\Adapter\Local;

use splitbrain\PHPArchive\Tar;
use splitbrain\PHPArchive\Archive;
use splitbrain\PHPArchive\FileInfo;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


//do not modify below
$that = "";
if(defined('XCLONER_PLUGIN_ACCESS') && XCLONER_PLUGIN_ACCESS)
{
	$that = $this;
}
$xcloner_restore = new Xcloner_Restore($that);

try{
	$return = $xcloner_restore->init();
	$xcloner_restore->send_response(200, $return);
}catch(Exception $e){
	$xcloner_restore->send_response(417, $e->getMessage());
}

class Xcloner_Restore
{
	
	const 	xcloner_minimum_version = "5.4.0";
	
	private $backup_archive_extensions 		= array("zip", "tar", "tgz", "tar.gz", "gz", "csv");
	private $process_files_limit 			= 150;
	private $process_files_limit_list 		= 350;
	private $process_mysql_records_limit 	= 250;
	private $adapter;
	private $filesystem;
	private $logger;
	private $backup_storage_dir;
	private $parent_api;
	
	
	public function __construct($parent_api = "")
	{
		register_shutdown_function(array($this, 'exception_handler'));

		if(defined('XCLONER_PLUGIN_ACCESS') && XCLONER_PLUGIN_ACCESS)
		{
			$dir = $parent_api->get_xcloner_container()->get_xcloner_settings()->get_xcloner_store_path();
		}
		
		if(!isset($dir) || !$dir){
			$dir = dirname(__FILE__);
		}
		
		$this->parent_api = $parent_api;
		
		$this->backup_storage_dir = $dir;
		
		$this->adapter = new Local($dir ,LOCK_EX, 'SKIP_LINKS');
		$this->filesystem = new Filesystem($this->adapter, new Config([
				'disable_asserts' => true,
			]));
			
		$this->logger = new Logger('xcloner_restore');
		
		$logger_path = $this->get_logger_filename();
		
		if(!is_writeable($logger_path) and !touch($logger_path))
		{
			$logger_path = "php://stderr";
		}
		
		$this->logger->pushHandler(new StreamHandler($logger_path, Logger::DEBUG));
		
		if(isset($_POST['API_ID'])){
			$this->logger->info("Processing ajax request ID ".substr(filter_input(INPUT_POST, 'API_ID', FILTER_SANITIZE_STRING), 0 , 15));
		}

	}
	
	public function exception_handler() {
		
		$error = error_get_last();
		
		if($error['type'] and $this->logger)
		{
			$this->logger->info($this->friendly_error_type ($error['type']).": ".var_export($error, true));
		}
	
	}
	
	private function friendly_error_type($type) {
	    static $levels=null;
	    if ($levels===null) {
	        $levels=[];
	        foreach (get_defined_constants() as $key=>$value) {
	            if (strpos($key,'E_')!==0) {continue;}
					$levels[$value]= $key; //substr($key,2);
	        }
	    }
	    return (isset($levels[$type]) ? $levels[$type] : "Error #{$type}");
	}
	
	public function get_logger_filename()
	{
		$filename = $this->backup_storage_dir .DS. "xcloner_restore.log";
		
		return $filename;
	}
	
	public function init()
	{
		if(isset($_POST['xcloner_action']) and $_POST['xcloner_action'])
		{
			$method = filter_input(INPUT_POST, 'xcloner_action', FILTER_SANITIZE_STRING);
			
			//$method = "list_backup_archives";
			
			$method .= "_action";
			
			if(method_exists($this, $method))
			{
				$this->logger->debug(sprintf('Starting action %s', $method));
				return call_user_func(array($this, $method));
				
			}else{
				throw new Exception($method ." does not exists");
				}
		}
		
		return $this->check_system();
	}
	
	public function write_file_action()
	{
		if(isset($_POST['file']))
		{
			$target_file = filter_input(INPUT_POST, 'file', FILTER_SANITIZE_STRING);
			
			if(!$_POST['start'])
				$fp = fopen($target_file, "wb+");
			else
				$fp = fopen($target_file, "ab+");	
			
			if(!$fp)
				throw new Exception("Unable to open $target_file file for writing");
			
			fseek($fp, $_POST['start']);
			
			if(isset($_FILES['blob']))
			{
				$this->logger->debug(sprintf('Writing %s bytes to file %s starting position %s using FILES blob', filesize($_FILES['blob']['tmp_name']), $target_file, $_POST['start']));
				
				$blob = file_get_contents($_FILES['blob']['tmp_name']);
				
				if(!$bytes_written = fwrite($fp, $blob))
					throw new Exception("Unable to write data to file $target_file");
				
				@unlink($_FILES['blob']['tmp_name']);
			}elseif(isset($_POST['blob'])){
				$this->logger->debug(sprintf('Writing %s bytes to file %s starting position %s using POST blob', strlen($_POST['blob']), $target_file, $_POST['start']));
				
				$blob = $_POST['blob'];

				if(!$bytes_written = fwrite($fp, $blob))
					throw new Exception("Unable to write data to file $target_file");
			}else{
				throw new Exception("Upload failed, did not receive any binary data");
			}
			
			fclose($fp);
		}
		
		return $bytes_written;
		
	}
	
	public function mysql_connect($remote_mysql_host, $remote_mysql_user, $remote_mysql_pass, $remote_mysql_db )
	{
		$this->logger->info(sprintf('Connecting to mysql database %s with %s@%s', $remote_mysql_db, $remote_mysql_user, $remote_mysql_host));

		$mysqli = new mysqli($remote_mysql_host, $remote_mysql_user, $remote_mysql_pass, $remote_mysql_db);

		if ($mysqli->connect_error) {
			throw new Exception('Connect Error (' . $mysqli->connect_errno . ') '
				. $mysqli->connect_error);
		}
		
		$mysqli->query("SET sql_mode='';");
		$mysqli->query("SET foreign_key_checks = 0;");
		if(isset($_REQUEST['charset_of_file']) and $_REQUEST['charset_of_file'])
			$mysqli->query("SET NAMES ".$_REQUEST['charset_of_file']."");
		else
			$mysqli->query("SET NAMES utf8;");
			
		return $mysqli;	
	}
	
	public function restore_mysql_backup_action()
	{
		$mysqldump_file 	= filter_input(INPUT_POST, 'mysqldump_file', FILTER_SANITIZE_STRING);
		$remote_path 		= filter_input(INPUT_POST, 'remote_path', FILTER_SANITIZE_STRING);
		$remote_mysql_user 	= filter_input(INPUT_POST, 'remote_mysql_user', FILTER_SANITIZE_STRING);
		$remote_mysql_pass 	= filter_input(INPUT_POST, 'remote_mysql_pass', FILTER_SANITIZE_STRING);
		$remote_mysql_db 	= filter_input(INPUT_POST, 'remote_mysql_db', FILTER_SANITIZE_STRING);
		$remote_mysql_host 	= filter_input(INPUT_POST, 'remote_mysql_host', FILTER_SANITIZE_STRING);
		$execute_query 		= trim(stripslashes($_POST['query']));
		$error_line			= filter_input(INPUT_POST, 'error_line', FILTER_SANITIZE_NUMBER_INT);
		$start			 	= filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		
		$wp_home_url 		= filter_input(INPUT_POST, 'wp_home_url', FILTER_SANITIZE_STRING);
		$remote_restore_url = filter_input(INPUT_POST, 'remote_restore_url', FILTER_SANITIZE_STRING);
		
		$wp_site_url 		= filter_input(INPUT_POST, 'wp_site_url', FILTER_SANITIZE_STRING);
		$restore_site_url 	= filter_input(INPUT_POST, 'restore_site_url', FILTER_SANITIZE_STRING);
		
		$mysql_backup_file = $remote_path.DS.$mysqldump_file;
		
		if(!file_exists($mysql_backup_file))
			throw new Exception(sprintf("Mysql backup file %s does not exists",$mysql_backup_file));
		
		
		/*if(defined('XCLONER_PLUGIN_ACCESS') && XCLONER_PLUGIN_ACCESS)
		{
			global $wpdb;
			//$mysqli = $this->parent_api->get_xcloner_container()->get_xcloner_database();
			$remote_mysql_host 	= $wpdb->dbhost;
			$remote_mysql_user 	= $wpdb->dbuser;
			$remote_mysql_pass 	= $wpdb->dbpassword;
			$remote_mysql_db 	= $wpdb->dbname;
		}*/
		
		{
			$mysqli = $this->mysql_connect($remote_mysql_host, $remote_mysql_user, $remote_mysql_pass, $remote_mysql_db );
		}
		
		$line_count = 0;
		$query = "";
		$return['finished'] = 1;
		$return['backup_file']	= $mysqldump_file;
		$return['backup_size']	= filesize($mysql_backup_file);
		
		$fp = fopen($mysql_backup_file, "r");
		if($fp)
		{
			$this->logger->info(sprintf("Opening mysql dump file %s at position %s.", $mysql_backup_file, $start));
			fseek($fp, $start);
			while ($line_count <= $this->process_mysql_records_limit and ($line = fgets($fp)) !== false) {
			// process the line read.
									
				//check if line is comment
				if(substr($line, 0, 1) == "#")
					continue;
				
				//check if line is empty	
				if($line == "\n" or trim($line) == "")
					continue;
					
				if(substr($line, strlen($line)-2, strlen($line)) == ";\n")
					$query .= $line;
				else{
					$query .= $line;
					continue;
				}
				
				if($execute_query)
				{
					$query  = (($execute_query));
					$execute_query = "";
				}	
				
				//Doing serialized url replace here
				
				if($wp_site_url and $wp_home_url and strlen($wp_home_url) < strlen($wp_site_url))
				{
					list($wp_home_url,$wp_site_url) 			= array($wp_site_url,$wp_home_url);
					list($remote_restore_url,$restore_site_url) = array($restore_site_url,$remote_restore_url);
					
				}
				
				if($wp_home_url and $remote_restore_url and strpos($query, $wp_home_url) !== false)
				{
					$query = $this->url_replace($wp_home_url, $remote_restore_url, $query);
				}
				
				if($wp_site_url and $restore_site_url and strpos($query, $wp_site_url) !== false)
				{
					$query = $this->url_replace($wp_site_url, $restore_site_url, $query);
				}
				
				if(!$mysqli->query($query) && !stristr($mysqli->error,"Duplicate entry"))
				{
					//$return['error_line'] = $line_count;
					$return['start'] = ftell($fp)-strlen($line);
					$return['query_error'] = true;
					$return['query'] = $query;
					$return['message'] = sprintf("Mysql Error: %s\n", $mysqli->error);
					
					$this->logger->error($return['message']);
					
					$this->send_response(418, $return);
					//throw new Exception(sprintf("Mysql Error: %s\n Mysql Query: %s", $mysqli->error, $query));
				}
				//else echo $line;
					
				$query = "";

				$line_count++;
				
			}
		}
		
		$return['start'] = ftell($fp);
		
		$this->logger->info(sprintf("Executed %s queries of size %s bytes", $line_count, ($return['start']-$start)));
		
		if(!feof($fp))
		{
			$return['finished'] = 0;
		}else{
			$this->logger->info(sprintf("Mysql Import Done."));
		}
		
		fclose($fp);
		
		$this->send_response(200, $return);
	}
	
	private function url_replace($search, $replace, $query)
	{
		$this->logger->info(sprintf("Doing url replace on query with length %s", strlen($query)), array("QUERY_REPLACE"));
		$query = str_replace($search, $replace, $query);
		$original_query = $query;
		
		if($this->has_serialized($query))
		{
			$this->logger->info(sprintf("Query contains serialized data, doing serialized size fix"), array("QUERY_REPLACE"));
			$query = $this->do_serialized_fix($query);
			
			if(!$query)
			{
				$this->logger->info(sprintf("Serialization probably failed here..."), array("QUERY_REPLACE"));
				$query = $original_query;
			}
		}
		$this->logger->info(sprintf("New query length is %s", strlen($query)), array("QUERY_REPLACE"));
		
		return $query;
	}
	
	public function list_backup_files_action()
	{
		$backup_parts = array();
		
		$source_backup_file = filter_input(INPUT_POST, 'file', FILTER_SANITIZE_STRING);
		$start 				= (int)filter_input(INPUT_POST, 'start', FILTER_SANITIZE_STRING);
		$return['part'] 	= (int)filter_input(INPUT_POST, 'part', FILTER_SANITIZE_STRING);
		
		$backup_file = $source_backup_file;
		
		if($this->is_multipart($backup_file))
		{
			$backup_parts = $this->get_multipart_files($backup_file);
			$backup_file = $backup_parts[$return['part']];
		}
		
		try{
			$tar = new Tar();
			$tar->open($this->backup_storage_dir.DS.$backup_file, $start);
		
			$data = $tar->contents($this->process_files_limit_list);
		}catch(Exception $e)
		{
			$return['error'] = true;
			$return['message'] = $e->getMessage();
			$this->send_response(200, $return);
		}
		
		$return['files'] 		= array();
		$return['finished'] 	= 1;
		$return['total_size'] 	= filesize($this->backup_storage_dir.DS.$backup_file);
		$i = 0;
		
		if(isset($data['extracted_files']) and is_array($data['extracted_files']))
		{
			foreach($data['extracted_files'] as $file)
			{
				$return['files'][$i]['path'] = $file->getPath();
				$return['files'][$i]['size'] = $file->getSize();
				$return['files'][$i]['mtime'] = date("d M,Y H:i", $file->getMtime());
				
				$i++;
			}
		}
		
		if(isset($data['start']))
		{
			$return['start'] = $data['start'];
			$return['finished'] = 0;	
		}else{
			if($this->is_multipart($source_backup_file))
			{
				$return['start'] = 0;
				
				++$return['part'];
			
				if($return['part'] < sizeof($backup_parts))	
					$return['finished'] = 0;
				
			}
		}	
		
		$this->send_response(200, $return);
	}
	
	public function restore_finish_action()
	{
		$remote_path 		= filter_input(INPUT_POST, 'remote_path', FILTER_SANITIZE_STRING);
		
		$wp_home_url 		= filter_input(INPUT_POST, 'wp_home_url', FILTER_SANITIZE_STRING);
		$remote_restore_url = filter_input(INPUT_POST, 'remote_restore_url', FILTER_SANITIZE_STRING);
		
		$remote_mysql_user 	= filter_input(INPUT_POST, 'remote_mysql_user', FILTER_SANITIZE_STRING);
		$remote_mysql_pass 	= filter_input(INPUT_POST, 'remote_mysql_pass', FILTER_SANITIZE_STRING);
		$remote_mysql_db 	= filter_input(INPUT_POST, 'remote_mysql_db', FILTER_SANITIZE_STRING);
		$remote_mysql_host 	= filter_input(INPUT_POST, 'remote_mysql_host', FILTER_SANITIZE_STRING);
		
		$update_remote_site_url			 	= filter_input(INPUT_POST, 'update_remote_site_url', FILTER_SANITIZE_NUMBER_INT);
		$delete_restore_script			 	= filter_input(INPUT_POST, 'delete_restore_script', FILTER_SANITIZE_NUMBER_INT);
		$delete_backup_temporary_folder		= filter_input(INPUT_POST, 'delete_backup_temporary_folder', FILTER_SANITIZE_NUMBER_INT);
				
		if($update_remote_site_url)
		{
			$mysqli = $this->mysql_connect($remote_mysql_host, $remote_mysql_user, $remote_mysql_pass, $remote_mysql_db );
			$this->update_wp_config($remote_path, $remote_mysql_host, $remote_mysql_user, $remote_mysql_pass, $remote_mysql_db);
			$this->update_wp_url($remote_path, $remote_restore_url, $mysqli);
		}
		
		if($delete_backup_temporary_folder)
		{
			$this->delete_backup_temporary_folder($remote_path);
		}
		
		if(!defined('XCLONER_PLUGIN_ACCESS') || XCLONER_PLUGIN_ACCESS != 1)
		{
			if($delete_restore_script)
			{
				$this->delete_self();
			}
		}
		
		$return = "Restore Process Finished.";
		$this->send_response(200, $return);
	}
	
	private function delete_backup_temporary_folder($remote_path)
	{
		$this->target_adapter = new Local($remote_path ,LOCK_EX, 'SKIP_LINKS');
		$this->target_filesystem = new Filesystem($this->target_adapter, new Config([
				'disable_asserts' => true,
			]));
			
		$mysqldump_list = array();
		$list = $this->target_filesystem->listContents();
		
		foreach($list as $file)
		{
			$matches = array();
			
			if($file['type'] == "dir")
			{
				if(preg_match("/xcloner-(\w*)/", $file['basename'], $matches)){
					$this->logger->info(sprintf('Deleting temporary folder %s', $file['path']));
					$this->target_filesystem->deleteDir($file['path']);
				}
			}
		}
		
		return true;
	
	}
	
	private function delete_self()
	{
		if($this->filesystem->has("vendor.phar"))
		{
			$this->logger->info(sprintf('Deleting vendor.phar'));
			$this->filesystem->delete("vendor.phar");
		}
		if($this->filesystem->has("vendor"))
		{
			$this->logger->info(sprintf('Deleting vendor folder'));
			$this->filesystem->deleteDir("vendor");
		}
		if($this->filesystem->has("xcloner_restore.php"))
		{
			$this->logger->info(sprintf('Deleting xcloner_restore.php'));
			$this->filesystem->delete("xcloner_restore.php");
		}
		
		if($this->filesystem->has("xcloner_restore.log"))
		{
			$this->logger->info(sprintf('Deleting xcloner_restore.log'));
			$this->filesystem->delete("xcloner_restore.log");
		}
		
		if($this->filesystem->has($this->get_logger_filename()))
		{
			$this->logger->info(sprintf('Deleting logger file %s', $this->get_logger_filename()));
			$this->filesystem->delete($this->get_logger_filename());
		}
		
	}
	
	private function update_wp_url($wp_path, $url, $mysqli)
	{
		$wp_config = $wp_path.DS."wp-config.php";
		
		$this->logger->info(sprintf('Updating site url to %s', $url));
		
		if(file_exists($wp_config))
		{
			$config = file_get_contents($wp_config);
			preg_match("/.*table_prefix.*=.*'(.*)'/i", $config, $matches);
			if(isset($matches[1]))
				$table_prefix = $matches[1];
			else
				throw new Exception("Could not load wordpress table prefix from wp-config.php file.");
		}
		else
			throw new Exception("Could not update the SITEURL and HOME, wp-config.php file not found");
			
		if(!$mysqli->query("update ".$table_prefix."options set option_value='".($url)."' where option_name='home'"))
			throw new Exception(sprintf("Could not update the HOME option, error: %s\n", $mysqli->error));
		
		if(!$mysqli->query("update ".$table_prefix."options set option_value='".($url)."' where option_name='siteurl'"))
			throw new Exception(sprintf("Could not update the SITEURL option, error: %s\n", $mysqli->error));
		
		return true;
	}
	
	private function update_wp_config($remote_path, $remote_mysql_host, $remote_mysql_user, $remote_mysql_pass, $remote_mysql_db)
	{
		$wp_config = $remote_path.DS."wp-config.php";
		
		if(!file_exists($wp_config))
		{
			throw new Exception("Could not find the wp-config.php in ".$remote_path);
		}
		
		$content = file_get_contents($wp_config);
		
		$content = preg_replace("/(?<=DB_NAME', ')(.*?)(?='\);)/", $remote_mysql_db, $content);
		$content = preg_replace("/(?<=DB_USER', ')(.*?)(?='\);)/", $remote_mysql_user, $content);
		$content = preg_replace("/(?<=DB_PASSWORD', ')(.*?)(?='\);)/", $remote_mysql_pass, $content);
		$content = preg_replace("/(?<=DB_HOST', ')(.*?)(?='\);)/", $remote_mysql_host, $content);
		
		$file_perms = fileperms($wp_config);
		
		chmod($wp_config, 0777);
		
		$this->logger->info(sprintf('Updating wp-config.php file with the new mysql details'));
		
		if(!file_put_contents($wp_config, $content))
			throw new Exception("Could not write updated config data to ".$wp_config);
		
		chmod($wp_config, $file_perms);
		
		return $wp_config;
		
	}
	
	public function list_mysqldump_backups_action()
	{
		$source_backup_file = filter_input(INPUT_POST, 'backup_file', FILTER_SANITIZE_STRING);
		$remote_path = filter_input(INPUT_POST, 'remote_path', FILTER_SANITIZE_STRING);
	
		$hash = $this->get_hash_from_backup($source_backup_file);	
		
		$this->target_adapter = new Local($remote_path ,LOCK_EX, 'SKIP_LINKS');
		$this->target_filesystem = new Filesystem($this->target_adapter, new Config([
				'disable_asserts' => true,
			]));
			
		$mysqldump_list = array();
		$list = $this->target_filesystem->listContents();
		
		foreach($list as $file)
		{
			$matches = array();
			
			if($file['type'] == "dir")
			{
				if(preg_match("/xcloner-(\w*)/", $file['basename'], $matches))
				{
					$files = $this->target_filesystem->listContents($file['basename']);
					foreach($files as $file)
					{
						if($file['extension'] == "sql")
						{
							$this->logger->info(sprintf('Found %s mysql backup file', $file['path']));
							$mysqldump_list[$file['path']]['path'] = $file['path'];
							$mysqldump_list[$file['path']]['size'] = $file['size'];
							$mysqldump_list[$file['path']]['timestamp'] = date("d M,Y H:i",$file['timestamp']);
							
							if($hash and $hash == $matches[1])
								$mysqldump_list[$file['path']]['selected'] = "selected";
							else
								$mysqldump_list[$file['path']]['selected'] = "";	
						}
					}
				}
			}	
		}
		
		$this->sort_by($mysqldump_list, 'timestamp','desc');
		$return['files'] = $mysqldump_list;
		
		$this->send_response(200, $return);
	}
	
	private function get_hash_from_backup($backup_file)
	{
		if(!$backup_file)
			return false;
			
		$result = preg_match("/-(\w*)./", substr($backup_file, strlen($backup_file)-10, strlen($backup_file)), $matches)	;
		
		if($result and isset($matches[1]))
			return ($matches[1]);
		
		return false;
	}
	
	public function list_backup_archives_action()
	{
		$local_backup_file = filter_input(INPUT_POST, 'local_backup_file', FILTER_SANITIZE_STRING);
		$list = $this->filesystem->listContents();
		
		$backup_files = array();
		$parents = array();
		
		foreach($list as $file_info)
		{
			$data = array();
			
			if(isset($file_info['extension']) and $file_info['extension'] == "csv")
			{
				$lines = explode(PHP_EOL, $this->filesystem->read($file_info['path']));
				foreach($lines as $line)
					if($line)
					{
						$data = str_getcsv($line);
						if(is_array($data)){
							$parents[$data[0]] = $file_info['path'];
							$file_info['childs'][] = $data;
							$file_info['size'] += $data[2];
						}
					}
						
			}
			
			if($file_info['type'] == 'file' and isset($file_info['extension']) and in_array($file_info['extension'], $this->backup_archive_extensions))
				$backup_files[$file_info['path']] = $file_info;
		}
		
		$new_list = array();
		
		foreach($backup_files as $key=>$file_info)
		{
			if(isset($parents[$file_info['path']]))
				$backup_files[$key]['parent'] = $parents[$file_info['path']];
			else{
				
				if($local_backup_file and ($file_info['basename'] == $local_backup_file))
					$file_info['selected'] = 'selected';
				
				$this->logger->info(sprintf('Found %s backup file', $file_info['path']));
					
				$new_list[$key] = $file_info;
			}
				
		}
		
		$this->sort_by($new_list, "timestamp","desc");
		
		$return['files'] = $new_list;
		
		$this->send_response(200, $return);
		
	}
	
	public function restore_backup_to_path_action()
	{
		$source_backup_file 	= filter_input(INPUT_POST, 'backup_file', FILTER_SANITIZE_STRING);
		$remote_path 			= filter_input(INPUT_POST, 'remote_path', FILTER_SANITIZE_STRING);
		$include_filter_files 	= filter_input(INPUT_POST, 'filter_files', FILTER_SANITIZE_STRING);
		$exclude_filter_files 	= "";
		$start 					= filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		$return['part'] 		= (int)filter_input(INPUT_POST, 'part', FILTER_SANITIZE_NUMBER_INT);
		$return['processed'] 	= (int)filter_input(INPUT_POST, 'processed', FILTER_SANITIZE_NUMBER_INT);
				
		$this->target_adapter = new Local($remote_path ,LOCK_EX, 'SKIP_LINKS');
		$this->target_filesystem = new Filesystem($this->target_adapter, new Config([
				'disable_asserts' => true,
			]));
		
		$backup_file = $source_backup_file;
		
		$return['finished'] = 1;
		$return['extracted_files'] = array();
		$return['total_size'] = $this->get_backup_size($backup_file);
		
		$backup_archive = new Tar();
		if($this->is_multipart($backup_file))
		{
			if(!$return['part'])
				$return['processed'] += $this->filesystem->getSize($backup_file);
				
			$backup_parts = $this->get_multipart_files($backup_file);
			$backup_file = $backup_parts[$return['part']];	
		}	
		
		$this->logger->info(sprintf('Opening backup archive %s at position %s', $backup_file, $start));
		$backup_archive->open($this->backup_storage_dir .DS. $backup_file, $start);

		$data = $backup_archive->extract($remote_path, '',$exclude_filter_files,$include_filter_files, $this->process_files_limit);
		
		if(isset($data['extracted_files']))
		{
			foreach($data['extracted_files'] as $spl_fileinfo)
			{
				$this->logger->info(sprintf('Extracted %s file', $spl_fileinfo->getPath()));
				$return['extracted_files'][] = $spl_fileinfo->getPath()." (".$spl_fileinfo->getSize()." bytes)";
			}
		}
		
		if(isset($data['start']))
		//if(isset($data['start']) and $data['start'] <= $this->filesystem->getSize($backup_file))
		{
			$return['finished'] = 0;
			$return['start'] = $data['start'];
		}else{
			
			$return['processed'] += $start;
			
			if($this->is_multipart($source_backup_file))
			{
				$return['start'] = 0;
				
				++$return['part'];
			
				if($return['part'] < sizeof($backup_parts))	
					$return['finished'] = 0;
				
			}
		}
		
		if($return['finished'])
			$this->logger->info(sprintf('Done extracting %s', $source_backup_file));
		
		$return['backup_file'] = $backup_file;
		
		$this->send_response(200, $return);
	}
	
	public function get_current_directory_action()
	{	
		global $wpdb;
		
		$restore_script_url = filter_input(INPUT_POST, 'restore_script_url', FILTER_SANITIZE_STRING);
		
		$pathinfo = pathinfo( __FILE__);
		
		$suffix = "";
		$return['remote_mysql_host'] 	= "localhost";
		$return['remote_mysql_user'] 	= "";
		$return['remote_mysql_pass'] 	= "";
		$return['remote_mysql_db'] 		= "";
		
		if(defined('XCLONER_PLUGIN_ACCESS') && XCLONER_PLUGIN_ACCESS)
		{
			$return['dir'] = realpath(get_home_path().DS.$suffix);
			$return['restore_script_url']  	= get_site_url();
			$return['remote_mysql_host'] 	= $wpdb->dbhost;
			$return['remote_mysql_user'] 	= $wpdb->dbuser;
			$return['remote_mysql_pass'] 	= $wpdb->dbpassword;
			$return['remote_mysql_db'] 		= $wpdb->dbname;
		}
		else{
			$return['dir'] = ($pathinfo['dirname']).DS.$suffix;
			$return['restore_script_url'] = str_replace($pathinfo['basename'], "", $restore_script_url).$suffix;
		}	
		
		$this->logger->info(sprintf('Determining current url as %s and path as %s', $return['dir'], $return['restore_script_url']));
		
		$this->send_response(200, $return);
	}
	
	public function check_system()
	{
		//check if i can write
		$tmp_file = md5(time());
		if(!file_put_contents($tmp_file, "++"))
			throw new Exception("Could not write to new host");
		
		if(!unlink($tmp_file))
			throw new Exception("Could not delete temporary file from new host");
		
		$max_upload      = $this->return_bytes((ini_get('upload_max_filesize')));
		$max_post        = $this->return_bytes((ini_get('post_max_size')));

		$return['max_upload_size'] = min($max_upload, $max_post); // bytes
		$return['status']		= true;
		
		$this->logger->info(sprintf('Current filesystem max upload size is %s bytes', $return['max_upload_size']));
		
		$this->send_response(200, $return);
	}
	
	private function return_bytes($val) {
	    $val = trim($val);
	    $last = strtolower($val[strlen($val)-1]);
	    switch($last) {
	        // The 'G' modifier is available since PHP 5.1.0
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }
	
	    return $val;
	}
	
	public function is_multipart($backup_name)
	{
		if(stristr($backup_name, "-multipart"))
			return true;
		
		return false;	
	}
	
	public function get_backup_size($backup_name)
	{
		$backup_size = $this->filesystem->getSize($backup_name);
		if($this->is_multipart($backup_name))
		{
			$backup_parts = $this->get_multipart_files($backup_name);
			foreach($backup_parts as $part_file)
				$backup_size += $this->filesystem->getSize($part_file);
		}
		
		return $backup_size;
	}
	
	public function get_multipart_files($backup_name)
	{
		$files = array();
		
		if($this->is_multipart($backup_name))
		{
			$lines = explode(PHP_EOL, $this->filesystem->read($backup_name));
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
	
	private function sort_by( &$array, $field, $direction = 'asc')
	{
		$direction = strtolower($direction);
		
	    usort($array, create_function('$a, $b', '
	        $a = $a["' . $field . '"];
	        $b = $b["' . $field . '"];
	
	        if ($a == $b)
	        {
	            return 0;
	        }
	
	        return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
	    '));
	
	    return true;
	}
		
	public static function send_response($status = 200, $response)
	{
		header("Access-Control-Allow-Origin: *");
		header("HTTP/1.1 200");
		header('Content-Type: application/json');
		$return['status'] = $status;
		$return['statusText'] = $response;
		
		if(isset($response['error']) && $response['error'])
		{
			$return['statusText'] = $response['message'];
			$return['error'] = true;
		}elseif($status != 200 and $status != 418)
		{
			$return['error'] = true;
			$return['message'] = $response;
		}
		
		echo json_encode($return);
		exit;
	}
	
	/*
	 * Serialize fix methods below for mysql query lines
	 */ 
	 
	function do_serialized_fix($query)
	{
		$query = str_replace(array("\\n","\\r","\\'"), array("","","\""), ($query));
		
		return preg_replace_callback('!s:(\d+):([\\\\]?"[\\\\]?"|[\\\\]?"((.*?)[^\\\\])[\\\\]?");!', function ($m) {
				  $data = "";
				  	
				  if(!isset($m[3]))
					$m[3] = "";
					
					$data = 's:'.strlen(($m[3])).':\"'.($m[3]).'\";';
	              //return $this->unescape_quotes($data);
	              
	              return $data;
	            }, $query);
	}
	
	private function unescape_quotes($value) {
		return str_replace('\"', '"', $value);
	}
	
	private function unescape_mysql($value) {
		return str_replace(array("\\\\", "\\0", "\\n", "\\r", "\Z",  "\'", '\"'),
						   array("\\",   "\0",  "\n",  "\r",  "\x1a", "'", '"'), 
						   $value);
	}	
	
	
	private function has_serialized($s)
	{
		if(
		    stristr($s, '{' ) != false &&
		    stristr($s, '}' ) != false &&
		    stristr($s, ';' ) != false &&
		    stristr($s, ':' ) != false
		    ){
		    return true;
		}else{
		    return false;
		}

	}
}

