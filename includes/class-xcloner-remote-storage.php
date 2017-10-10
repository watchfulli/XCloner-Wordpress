<?php
use League\Flysystem\Config;
use League\Flysystem\Filesystem;

use League\Flysystem\Adapter\Ftp as Adapter;

use League\Flysystem\Sftp\SftpAdapter;

#use League\Flysystem\Dropbox\DropboxAdapter;
#use Dropbox\Client;
use Srmklive\Dropbox\Client\DropboxClient;
use Srmklive\Dropbox\Adapter\DropboxAdapter;

use MicrosoftAzure\Storage\Common\ServicesBuilder;
use League\Flysystem\Azure\AzureAdapter;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

use Mhetreramesh\Flysystem\BackblazeAdapter;
use ChrisWhite\B2\Client as B2Client;

use Sabre\DAV\Client as SabreClient;
use League\Flysystem\WebDAV\WebDAVAdapter;

class Xcloner_Remote_Storage{
	
	private $gdrive_app_name 		= "XCloner Backup and Restore";
	
	private $storage_fields = array(
					"option_prefix" => "xcloner_",
					"ftp" => array(
						"text"				=> "FTP",
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
						"text"				=> "SFTP",
						"sftp_enable"		=> "int",
						"sftp_hostname" 	=> "string",
						"sftp_port" 		=> "int",
						"sftp_username" 	=> "string",
						"sftp_password" 	=> "raw",
						"sftp_path" 		=> "path",
						"sftp_private_key" 	=> "raw",
						"sftp_timeout" 		=> "int",
						"sftp_cleanup_days"	=> "float",
						),
					"aws" => array(
						"text"					=> "AWS",
						"aws_enable"			=> "int",
						"aws_key"				=> "string",
						"aws_secret"			=> "string",
						"aws_region"			=> "string",
						"aws_bucket_name"		=> "string",
						"aws_cleanup_days"		=> "float",		
						),		
					"dropbox" => array(
						"text"					=> "Dropbox",
						"dropbox_enable"		=> "int",
						"dropbox_access_token"	=> "string",
						"dropbox_app_secret"	=> "string",
						"dropbox_prefix"		=> "string",
						"dropbox_cleanup_days"	=> "float",		
						),	
					"azure" => array(
						"text"					=> "Azure BLOB",
						"azure_enable"			=> "int",
						"azure_account_name"	=> "string",
						"azure_api_key"			=> "string",
						"azure_container"		=> "string",
						"azure_cleanup_days"	=> "float",
						),
					"backblaze" => array(
						"text"						=> "Backblaze",
						"backblaze_enable"			=> "int",
						"backblaze_account_id"		=> "string",
						"backblaze_application_key"	=> "string",
						"backblaze_bucket_name"		=> "string",
						"backblaze_cleanup_days"	=> "float",		
						),		
						
					"webdav" => array(
						"text"						=> "WebDAV",
						"webdav_enable"			=> "int",
						"webdav_url"			=> "string",
						"webdav_username"		=> "string",
						"webdav_password"		=> "string",
						"webdav_target_folder"	=> "string",
						"webdav_cleanup_days"	=> "float",		
						),		
					
					"gdrive" => array(
						"text"						=> "Google Drive",
						"gdrive_enable"				=> "int",
						"gdrive_access_code"		=> "string",
						"gdrive_client_id"			=> "string",
						"gdrive_client_secret"		=> "string",
						"gdrive_target_folder"		=> "string",
						"gdrive_cleanup_days"		=> "float",		
						),	
					);
	
	private $aws_regions = array(							
								'us-east-1'=>'US East (N. Virginia)',
								'us-east-2'=>'US East (Ohio)',
								'us-west-1'=>'US West (N. California)',
								'us-west-2'=>'US West (Oregon)',
								'ca-central-1'=>'Canada (Central)',
								'eu-west-1'=>'EU (Ireland)',
								'eu-central-1'=>'EU (Frankfurt)',
								'eu-west-2'=>'EU (London)',
								'ap-northeast-1'=>'Asia Pacific (Tokyo)',
								'ap-northeast-2'=>'Asia Pacific (Seoul)',
								'ap-southeast-1'=>'Asia Pacific (Singapore)',
								'ap-southeast-2'=>'Asia Pacific (Sydney)',
								'ap-south-1'=>'Asia Pacific (Mumbai)',
								'sa-east-1'=>'South America (São Paulo)'
							);
	
	private $xcloner_sanitization;
	private $xcloner_file_system;
	private $logger;
	private $xcloner;
	
	public function __construct(Xcloner $xcloner_container)
	{
		$this->xcloner_sanitization 	= $xcloner_container->get_xcloner_sanitization();
		$this->xcloner_file_system 		= $xcloner_container->get_xcloner_filesystem();
		$this->logger 					= $xcloner_container->get_xcloner_logger()->withName("xcloner_remote_storage");
		$this->xcloner 					= $xcloner_container;
	}
	
	private function get_xcloner_container()
	{
		return $this->xcloner_container;
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
		if(!$action)
		{
			return false;
		}
		
		$storage = $this->xcloner_sanitization->sanitize_input_as_string($action);
		$this->logger->debug(sprintf("Saving the remote storage %s options", strtoupper($action)));	
		
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
					
				$sanitized_value = $this->xcloner_sanitization->$sanitize_method(stripslashes($_POST[$check_field]));
				update_option($check_field, $sanitized_value);
			}
			
			$this->xcloner->trigger_message(__("%s storage settings saved.", 'xcloner-backup-and-restore'), "success", ucfirst($action));
		}
		
	}
	
	public function check($action = "ftp")
	{
		try{
			$this->verify_filesystem($action);
			$this->xcloner->trigger_message(__("%s connection is valid.", 'xcloner-backup-and-restore'), "success", ucfirst($action));
			$this->logger->debug(sprintf("Connection to remote storage %s is valid", strtoupper($action)));	
		}catch(Exception $e){
			$this->xcloner->trigger_message("%s connection error: ".$e->getMessage(), "error", ucfirst($action));
		}
	}
	
	public function verify_filesystem($storage_type)
	{
		$method = "get_".$storage_type."_filesystem";
		
		$this->logger->info(sprintf("Checking validity of the remote storage %s filesystem", strtoupper($storage_type)));	
		
		if(!method_exists($this, $method))
			return false;
		
		list($adapter, $filesystem) = $this->$method();
		
		$test_file = substr(".xcloner_".md5(time()), 0, 15);
		
		if($storage_type == "gdrive")
		{
			if(!is_array($filesystem->listContents()))
				throw new Exception(__("Could not read data",'xcloner-backup-and-restore'));
			$this->logger->debug(sprintf("I can list data from remote storage %s", strtoupper($storage_type)));	
			
			return true;
		}
			
		//testing write access
		if(!$filesystem->write($test_file, "data"))
			throw new Exception(__("Could not write data",'xcloner-backup-and-restore'));
		$this->logger->debug(sprintf("I can write data to remote storage %s", strtoupper($storage_type)));	
		
		//testing read access
		if(!$filesystem->has($test_file))
			throw new Exception(__("Could not read data",'xcloner-backup-and-restore'));
		$this->logger->debug(sprintf("I can read data to remote storage %s", strtoupper($storage_type)));		
		
		//delete test file
		if(!$filesystem->delete($test_file))
			throw new Exception(__("Could not delete data",'xcloner-backup-and-restore'));
		$this->logger->debug(sprintf("I can delete data to remote storage %s", strtoupper($storage_type)));		
		
		return true;
	}
	
	public function upload_backup_to_storage($file, $storage)
	{
		if(!$this->xcloner_file_system->get_storage_filesystem()->has($file))
		{
			$this->logger->info(sprintf("File not found %s in local storage", $file));
			return false;
		}
			
		$method = "get_".$storage."_filesystem";	
		
		if(!method_exists($this, $method))
			return false;
			
		list($remote_storage_adapter, $remote_storage_filesystem) = $this->$method();
		
		//doing remote storage cleaning here
		$this->clean_remote_storage($storage, $remote_storage_filesystem);
		
		$this->logger->info(sprintf("Transferring backup %s to remote storage %s", $file, strtoupper($storage)), array(""));
		
		/*if(!$this->xcloner_file_system->get_storage_filesystem()->has($file))
		{
			$this->logger->info(sprintf("File not found %s in local storage", $file));
			return false;
		}*/
		
		$backup_file_stream = $this->xcloner_file_system->get_storage_filesystem()->readStream($file);

		if(!$remote_storage_filesystem->writeStream($file, $backup_file_stream))
		{
			$this->logger->info(sprintf("Could not transfer file %s", $file));
			return false;
		}
		
		if($this->xcloner_file_system->is_multipart($file))
		{
			$parts = $this->xcloner_file_system->get_multipart_files($file);
			if(is_array($parts))
				foreach($parts as $part_file)
				{
					$this->logger->info(sprintf("Transferring backup %s to remote storage %s", $part_file, strtoupper($storage)), array(""));
					
					$backup_file_stream = $this->xcloner_file_system->get_storage_filesystem()->readStream($part_file);
					if(!$remote_storage_filesystem->writeStream($part_file, $backup_file_stream))
						return false;
				}
		}
		
		$this->logger->info(sprintf("Upload done, disconnecting from remote storage %s", strtoupper($storage)));	
				
		return true;
		
	}
	
	public function copy_backup_remote_to_local($file, $storage)
	{
		$method = "get_".$storage."_filesystem";	
		
		$target_filename = $file;
		
		if(!method_exists($this, $method))
			return false;
			
		list($remote_storage_adapter, $remote_storage_filesystem) = $this->$method();
		
		if(!$remote_storage_filesystem->has($file))
		{
			$this->logger->info(sprintf("File not found %s in remote storage %s", $file, strtoupper($storage)));
			return false;
		}
		
		if($storage == "gdrive")
		{
			$metadata = $remote_storage_filesystem->getMetadata($file);
			$target_filename = $metadata['filename'].".".$metadata['extension'];
		}
		
		$this->logger->info(sprintf("Transferring backup %s to local storage from %s storage", $file, strtoupper($storage)), array(""));
		
		$backup_file_stream = $remote_storage_filesystem->readStream($file);

		if(!$this->xcloner_file_system->get_storage_filesystem()->writeStream($target_filename, $backup_file_stream))
		{
			$this->logger->info(sprintf("Could not transfer file %s", $file));
			return false;
		}
		
		if($this->xcloner_file_system->is_multipart($target_filename))
		{
			$parts = $this->xcloner_file_system->get_multipart_files($file, $storage);
			if(is_array($parts))
				foreach($parts as $part_file)
				{
					$this->logger->info(sprintf("Transferring backup %s to local storage from %s storage", $part_file, strtoupper($storage)), array(""));
					
					$backup_file_stream = $remote_storage_filesystem->readStream($part_file);
					if(!$this->xcloner_file_system->get_storage_filesystem()->writeStream($part_file, $backup_file_stream))
						return false;
				}
		}
		
		$this->logger->info(sprintf("Upload done, disconnecting from remote storage %s", strtoupper($storage)));	
				
		return true;
		
	}
	
	public function clean_remote_storage($storage, $remote_storage_filesystem)
	{
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
	}
	
	public function get_azure_filesystem()
	{
		$this->logger->info(sprintf("Creating the AZURE BLOB remote storage connection"), array(""));
		
		if (version_compare(phpversion(), '5.5.0', '<')) 
		{
				throw new Exception("AZURE BLOB requires PHP 5.5 to be installed!");
		}
		
		if (!class_exists('XmlWriter')) 
		{
				throw new Exception("AZURE BLOB requires libxml PHP module to be installed with XmlWriter class enabled!");
		}
		
		$endpoint = sprintf(
		    'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
		    get_option("xcloner_azure_account_name"),
		    get_option("xcloner_azure_api_key")
		);
		
		$blobRestProxy = ServicesBuilder::getInstance()->createBlobService($endpoint);
		
		$adapter = new AzureAdapter($blobRestProxy, get_option("xcloner_azure_container"));
		
		$filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));

		return array($adapter, $filesystem);
	}
	
	public function get_dropbox_filesystem()
	{
		$this->logger->info(sprintf("Creating the DROPBOX remote storage connection"), array(""));
		
		if (version_compare(phpversion(), '5.5.0', '<')) 
		{
				throw new Exception("DROPBOX requires PHP 5.5 to be installed!");
		}
		
		$client = new DropboxClient(get_option("xcloner_dropbox_access_token"));
		$adapter = new DropboxAdapter($client, get_option("xcloner_dropbox_prefix"));
		
		$filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));

		return array($adapter, $filesystem);
	}
	
	public function get_aws_filesystem()
	{
		$this->logger->info(sprintf("Creating the AWS remote storage connection"), array(""));
		
		if (version_compare(phpversion(), '5.5.0', '<')) 
		{
				throw new Exception("AWS S3 class requires PHP 5.5 to be installed!");
		}
		
		if (!class_exists('XmlWriter')) 
		{
				throw new Exception("AZURE BLOB requires libxml PHP module to be installed with XmlWriter class enabled!");
		}
		
		
		$client = new S3Client([
		    'credentials' => [
		        'key'    => get_option("xcloner_aws_key"),
		        'secret' => get_option("xcloner_aws_secret")
		    ],
		    'region' => get_option("xcloner_aws_region"),
		    'version' => 'latest',
		]);
		
		$adapter = new AwsS3Adapter($client, get_option("xcloner_aws_bucket_name"));
		$filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));

		return array($adapter, $filesystem);
	}
	
	public function get_backblaze_filesystem()
	{
		$this->logger->info(sprintf("Creating the BACKBLAZE remote storage connection"), array(""));
		
		if (version_compare(phpversion(), '5.5.0', '<')) 
		{
				throw new Exception("BACKBLAZE API requires PHP 5.5 to be installed!");
		}
		
		
		$client = new B2Client(get_option("xcloner_backblaze_account_id"), get_option("xcloner_backblaze_application_key"));
		$adapter = new BackblazeAdapter($client, get_option("xcloner_backblaze_bucket_name"));
		
		$filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));

		return array($adapter, $filesystem);
	}
	
	public function get_webdav_filesystem()
	{
		$this->logger->info(sprintf("Creating the WEBDAV remote storage connection"), array(""));
		
		if (version_compare(phpversion(), '5.5.0', '<')) 
		{
				throw new Exception("WEBDAV API requires PHP 5.5 to be installed!");
		}
		
		$settings = array(
			'baseUri' 	=> get_option("xcloner_webdav_url"),
			'userName' 	=> get_option("xcloner_webdav_username"),
			'password' 	=> get_option("xcloner_webdav_password"),
			//'proxy' => 'locahost:8888',
		);
		
				
		$client = new SabreClient($settings);
		$adapter = new WebDAVAdapter($client, get_option("xcloner_webdav_target_folder"));
		$filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));
			
		return array($adapter, $filesystem);
	}
	
	
	public function gdrive_construct()
	{

		//if((function_exists("is_plugin_active") && !is_plugin_active("xcloner-google-drive/xcloner-google-drive.php")) || !file_exists(__DIR__ . "/../../xcloner-google-drive/vendor/autoload.php"))
		if(!class_exists('Google_Client'))
		{
			return false;
		}
		
		//require_once(__DIR__ . "/../../xcloner-google-drive/vendor/autoload.php");
		
		$client = new \Google_Client();
		$client->setApplicationName($this->gdrive_app_name);
		$client->setClientId(get_option("xcloner_gdrive_client_id"));
		$client->setClientSecret(get_option("xcloner_gdrive_client_secret"));
		
		//$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']."?page=xcloner_remote_storage_page&action=set_gdrive_code";
		$redirect_uri = "urn:ietf:wg:oauth:2.0:oob";
		
		$client->setRedirectUri($redirect_uri); //urn:ietf:wg:oauth:2.0:oob
		$client->addScope("https://www.googleapis.com/auth/drive");
		$client->setAccessType('offline');
		
		return $client;
	}
	
	public function get_gdrive_auth_url()
	{
		$client = $this->gdrive_construct();
		
		if(!$client)
			return false;
			
		return $authUrl = $client->createAuthUrl();
	}
	
	public function set_access_token($code)
	{
		$client = $this->gdrive_construct();
		
		if(!$client)
		{
			$error_msg = "Could not initialize the Google Drive Class, please check that the xcloner-google-drive plugin is enabled...";
			$this->logger->error($error_msg);
			return false;
		}

		$token = $client->fetchAccessTokenWithAuthCode($code);
		$client->setAccessToken($token);
		
		update_option("xcloner_gdrive_access_token", $token['access_token']);
		update_option("xcloner_gdrive_refresh_token", $token['refresh_token']);
		
		$redirect_url = ('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']."?page=xcloner_remote_storage_page#gdrive");
		
		?>
		<script>
			window.location='<?php echo $redirect_url?>';
		</script>
		<?php
		
	}
	
	/*
	 * php composer.phar remove nao-pon/flysystem-google-drive 
	 *
	 */
	public function get_gdrive_filesystem()
	{
		if (version_compare(phpversion(), '5.5.0', '<')) 
		{
				throw new Exception("Google Drive API requires PHP 5.5 to be installed!");
		}
		
		$this->logger->info(sprintf("Creating the Google Drive remote storage connection"), array(""));
		
		$client = $this->gdrive_construct();
		
		if(!$client)
		{
			$error_msg = "Could not initialize the Google Drive Class, please check that the xcloner-google-drive plugin is enabled...";
			$this->logger->error($error_msg);
			throw new Exception($error_msg);
		}
				
		$client->refreshToken(get_option("xcloner_gdrive_refresh_token"));
	
		$service = new \Google_Service_Drive($client);
		
		$parent = 'root';
		$dir = basename( get_option("xcloner_gdrive_target_folder"));
		
		$folderID = get_option("xcloner_gdrive_target_folder");
		
		$tmp = parse_url($folderID);
		
		if(isset($tmp['query']))
		{
			$folderID = str_replace("id=", "", $tmp['query']);
		}
		
		if(stristr($folderID, "/"))
		{
			$query = sprintf('mimeType = \'application/vnd.google-apps.folder\' and \'%s\' in parents and name contains \'%s\'', $parent, $dir);
			$response = $service->files->listFiles([
	                'pageSize' => 1,
	                'q' => $query
	            ]);
			
			if(sizeof($response))
			{
				foreach ($response as $obj) {
					$folderID =  $obj->getId();
				}
			}else{
				$this->xcloner->trigger_message(sprintf(__("Could not find folder ID by name %s", 'xcloner-backup-and-restore'), $folderID), "error");
			}
		}
		
		$this->logger->info(sprintf("Using target folder with ID %s on the remote storage", $folderID));
		
		$adapter = new \Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter($service, $folderID);
		
		$filesystem = new \League\Flysystem\Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));
			
		
		return array($adapter, $filesystem);	
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
	
	public function get_aws_regions()
	{
		return $this->aws_regions;
	}
	
}
