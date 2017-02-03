<?php
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Ftp as Adapter;
use League\Flysystem\Sftp\SftpAdapter;


class Xcloner_Remote_Storage{
	
	private $storage_fields = array(
					"option_prefix" => "xcloner_",
					"ftp" => array(
						"text"				=> "Ftp",
						"ftp_enable"		=> "int",
						"ftp_hostname" 		=> "string",
						"ftp_port" 			=> "int",
						"ftp_username" 		=> "string",
						"ftp_password" 		=> "raw",
						"ftp_path" 			=> "path",
						"ftp_transfer_mode" => "int",
						"ftp_ssl_mode"		=> "int",
						"ftp_timeout" 		=> "int",
						"ftp_cleanup_days"	=> "float",
						),
					"sftp" => array(
						"text"				=> "SFtp",
						"sftp_enable"		=> "int",
						"sftp_hostname" 	=> "string",
						"sftp_port" 		=> "int",
						"sftp_username" 	=> "string",
						"sftp_password" 	=> "raw",
						"sftp_path" 		=> "path",
						"sftp_private_key" 	=> "path",
						"sftp_timeout" 		=> "int",
						"sftp_cleanup_days"	=> "float",
						),
					);
	
	public function __construct($hash = "")
	{
		$this->xcloner_sanitization 	= new Xcloner_Sanitization($hash);
		$this->xcloner_file_system 		= new Xcloner_File_System($hash);
		$this->logger 					= new XCloner_Logger("xcloner_remote_storage", $hash);
		$this->xcloner = new Xcloner();
	}
	
	public function get_available_storages()
	{
		$return = array();
		foreach($this->storage_fields as $storage=>$data)
		{
			$check_field = $this->storage_fields["option_prefix"].$storage."_enable";
			if(get_option($check_field))
				$return[$storage] = $data['text'];
		}
		
		return $return;
	}
	
	public function save($action = "ftp")
	{
		$storage = $this->xcloner_sanitization->sanitize_input_as_string($action);
		
		
		if(is_array($this->storage_fields[$storage]))
		{
			foreach($this->storage_fields[$storage] as $field=>$validation)
			{
				$check_field = $this->storage_fields["option_prefix"].$field;
				$sanitize_method = "sanitize_input_as_".$validation;
				
				if(!isset($_POST[$check_field]))
					$_POST[$check_field] = 0;
				
				if(!method_exists($this->xcloner_sanitization, $sanitize_method))
					$sanitize_method = "sanitize_input_as_string";
					
				$sanitized_value = $this->xcloner_sanitization->$sanitize_method($_POST[$check_field]);
				update_option($check_field, $sanitized_value);
			}
			
			$this->xcloner->trigger_message(__("%s storage settings saved.", "xcloner"), "success", ucfirst($action));
		}
		
		if(isset($_POST['connection_check']) && $_POST['connection_check'] == "ftp_check")
		{
			try{
				$this->verify_ftp_filesystem();
				$this->xcloner->trigger_message(__("%s connection is valid.", "xcloner"), "success", ucfirst($action));
			}catch(Exception $e){
				$this->xcloner->trigger_message("%s connection error: ".$e->getMessage(), "error", ucfirst($action));
			}
		}elseif(isset($_POST['connection_check']) && $_POST['connection_check'] == "sftp_check")
		{
			try{
				$this->verify_sftp_filesystem();
				$this->xcloner->trigger_message(__("%s connection is valid.", "xcloner"), "success", ucfirst($action));
			}catch(Exception $e){
				$this->xcloner->trigger_message("%s connection error: ".$e->getMessage(), "error", ucfirst($action));
			}
		}
		
	}
	
	public function verify_filesystem($filesystem)
	{
		$test_file = substr(".xcloner_".md5(time()), 0, 15);
			
		//testing write access
		if(!$filesystem->write($test_file, "data"))
			throw new Exception(__("Could not write data","xcloner"));
		
		//testing read access
		if(!$filesystem->read($test_file))
			throw new Exception(__("Could not read data","xcloner"));
		
		//delete test file
		if(!$filesystem->delete($test_file))
			throw new Exception(__("Could not delete data","xcloner"));
	}
	
	public function verify_ftp_filesystem()
	{
		$filesystem = $this->get_ftp_filesystem();
		
		if($filesystem)
		{
			$this->verify_filesystem($filesystem);
		}
		
	}
	
	public function verify_sftp_filesystem()
	{
		$filesystem = $this->get_sftp_filesystem();
		
		if($filesystem)
		{
			$this->verify_filesystem($filesystem);
		}
		
	}
	
	public function upload_backup_to_storage($file, $storage)
	{
		if(!$this->xcloner_file_system->get_storage_filesystem()->has($file))
			return false;
			
		$method = "get_".$storage."_filesystem";	
		list($remote_storage_adapter, $remote_storage_filesystem) = $this->$method();
		
		$this->logger->info(sprintf("Transferring backup %s to remote storage %s", $file, strtoupper($storage)), array(""));
		
		$backup_file_stream = $this->xcloner_file_system->get_storage_filesystem()->readStream($file);
		if(!$remote_storage_filesystem->updateStream($file, $backup_file_stream))
			return false;
		
		if($this->xcloner_file_system->is_multipart($file))
		{
			$parts = $this->xcloner_file_system->get_multipart_files($file);
			if(is_array($parts))
				foreach($parts as $part_file)
				{
					$this->logger->info(sprintf("Transferring backup %s to remote storage %s", $part_file, strtoupper($storage)), array(""));
					
					$backup_file_stream = $this->xcloner_file_system->get_storage_filesystem()->readStream($part_file);
					if(!$remote_storage_filesystem->updateStream($part_file, $backup_file_stream))
						return false;
				}
		}
		
		$check_field = $this->storage_fields["option_prefix"].$storage."_cleanup_days";
		if($expire_days = get_option($check_field))
		{
			$this->logger->info(sprintf("Doing %s remote storage cleanup for %s days limit", strtoupper($storage), $expire_days));
			$files = $remote_storage_filesystem->listContents();
			
			$current_timestamp = strtotime("-".$expire_days." days");
			
			if(is_array($files))
			foreach($files as $file)
			{
				$file['timestamp'] = $remote_storage_filesystem->getTimestamp($file['path']);
				
				if($current_timestamp >= $file['timestamp'])
				{
					$remote_storage_filesystem->delete($file['path']);
					$this->logger->info("Deleting remote file ".$file['path']." matching rule", array("RETENTION LIMIT TIMESTAMP", $file['timestamp']." =< ".$expire_days));
				}
			
			}
		}
		
		$remote_storage_adapter->disconnect();
		
		return true;
		
	}
	
	public function get_ftp_filesystem()
	{
		$this->logger->info(sprintf("Creating the FTP remote storage connection"), array(""));
		
		$adapter = new Adapter([
		    'host' => get_option("xcloner_ftp_hostname"),
		    'username' => get_option("xcloner_ftp_username"),
		    'password' => get_option("xcloner_ftp_password"),
		
		    /** optional config settings */
		    'port' => get_option("xcloner_ftp_port", 21),
		    'root' => get_option("xcloner_ftp_path"),
		    'passive' => get_option("xcloner_ftp_transfer_mode"),
		    'ssl' => get_option("xcloner_ftp_ssl_mode"),
		    'timeout' => get_option("xcloner_ftp_timeout", 30),
		]);
		
		$adapter->connect();
		
		$filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));
		
		return array($adapter, $filesystem);
	}
	
	public function get_sftp_filesystem()
	{
		$this->logger->info(sprintf("Creating the SFTP remote storage connection"), array(""));
		
		$adapter = new SftpAdapter([
		    'host' => get_option("xcloner_sftp_hostname"),
		    'username' => get_option("xcloner_sftp_username"),
		    'password' => get_option("xcloner_sftp_password"),
		
		    /** optional config settings */
		    'port' => get_option("xcloner_sftp_port", 22),
		    'root' => get_option("xcloner_sftp_path"),
		    'privateKey' => get_option("xcloner_sftp_private_key"),
		    'timeout' => get_option("xcloner_ftp_timeout", 30),
		]);
		
		$adapter->connect();
		
		$filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));
		
		return array($adapter, $filesystem);
	}
	
	public function change_storage_status($field, $value)
	{
		$field = $this->xcloner_sanitization->sanitize_input_as_string($field);
		$value = $this->xcloner_sanitization->sanitize_input_as_int($value);

		return update_option($field, $value);
	}
	
}
