<?php

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
//use League\Flysystem\Util\ContentListingFormatter;
use League\Flysystem\Adapter\Local;

class Xcloner_Api{

	protected $db_link;
	protected $xcloner_settings;
	protected $xcloner_file_system;
	protected $xcloner_requirements;
	protected $_file_system;
	protected $exclude_files_by_default = array("administrator/backups", "wp-content/backups");
	protected $form_params;
	protected $logger;
	
	public function __construct()
	{
		$this->xcloner_settings 		= new Xcloner_Settings();
		$this->xcloner_file_system 		= new Xcloner_File_System();
		$this->xcloner_sanitization 	= new Xcloner_Sanitization();
		$this->xcloner_requirements 	= new XCloner_Requirements();
		
		$this->logger = new XCloner_Logger("xcloner_api");
		
	}
	
	public function init_db()
	{
		
		$data['dbHostname'] = $this->xcloner_settings->get_db_hostname();
		$data['dbUsername'] = $this->xcloner_settings->get_db_username();
		$data['dbPassword'] = $this->xcloner_settings->get_db_password();
		$data['dbDatabase'] = $this->xcloner_settings->get_db_database();
		
		
		$data['recordsPerSession'] 		= $this->xcloner_settings->get_xcloner_option('xcloner_database_records_per_request');
		$data['TEMP_DBPROCESS_FILE'] 	= $this->xcloner_settings->get_xcloner_tmp_path().DS.".database";
		$data['TEMP_DUMP_FILE'] 		= $this->xcloner_settings->get_xcloner_tmp_path().DS."database-sql.sql";
		
		try
		{
			$xcloner_db = new XCloner_Database($data['dbUsername'], $data['dbPassword'], $data['dbDatabase'], $data['dbHostname']);
			$xcloner_db->init($data);

		}catch(Exception $e)
		{
			$this->send_response($e->getMessage());
			$this->logger->error($e->getMessage());
			
		}
		
		$this->db_link = $xcloner_db;
		
	
	}
	
	public function scan_filesystem()
	{
		$params = json_decode(stripslashes($_POST['data']));
		$init 	= (int)$_POST['init'];
		
		if($params === NULL)
			 die( '{"status":false,"msg":"The post_data parameter must be valid JSON"}' );
			 
		$this->process_params($params);
		
		//print_r($this->form_params);
		
		$this->xcloner_file_system->set_excluded_files($this->form_params['excluded_files']);
		
		$return = $this->xcloner_file_system->start_file_recursion($init);
		
		$data["finished"] = !$return;
		$data["total_files_num"] = $this->xcloner_file_system->get_scanned_files_num();
		$data["last_logged_file"] = $this->xcloner_file_system->last_logged_file();
		$data["total_files_size"] = number_format($this->xcloner_file_system->get_scanned_files_total_size()/(1024*1024), 2);
		return $this->send_response($data);
	}
	
	private function process_params($params)
	{
		$this->form_params['system'] = array();
		
		if(isset($params->backup_params))
		{
			foreach($params->backup_params as $param)
			{
				$this->form_params['system'][$param->name] = $this->xcloner_sanitization->sanitize_input_as_string($param->value);
				$this->logger->info("Adding system param ".$param->value."\n", array('POST', 'system params'));
			}
		}
		
		$this->form_params['database'] = array();
		
		if(isset($params->table_params))
		{
			foreach($params->table_params as $param)
			{
				$this->form_params['database'][$param->parent][] = $this->xcloner_sanitization->sanitize_input_as_raw($param->id);
				$this->logger->info("Adding database filter ".$param->parent.".".$param->id."\n", array('POST', 'database filter'));
			}
		}
		
		$this->form_params['excluded_files'] =  $this->exclude_files_by_default;
		if(isset($params->files_params))
		{
			foreach($params->files_params as $param)
			{
				$this->form_params['excluded_files'][] = $this->xcloner_sanitization->sanitize_input_as_relative_path($param->id);
			}
			
			$unique_exclude_files = array();
			
			foreach($params->files_params as $key=>$param)
			{
				if(!in_array($param->parent, $this->form_params['excluded_files'])){
				//$this->form_params['excluded_files'][] = $this->xcloner_sanitization->sanitize_input_as_relative_path($param->id);
					$unique_exclude_files[] = $param->id;
					$this->logger->info("Adding file filter ".$param->id."\n", array('POST', 'exclude files filter'));
				}
			}
			$this->form_params['excluded_files'] = $unique_exclude_files;
			
		}
		
		return $this;
	}
	
	public function get_file_system_action()
	{
		if (!current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
		
		$folder = $this->xcloner_sanitization->sanitize_input_as_relative_path($_POST['id']);
		
		$data = array();
		
		if($folder == "#"){
			
			$folder = "/";
			//$list_directory = $this->xcloner_settings->get_xcloner_start_path();
			$data[] = array(
						'id' => $folder,
						'parent' => '#',
						'text' => $this->xcloner_settings->get_xcloner_start_path(),
						//'children' => true,
						'state' =>  array('selected' => false, 'opened' => true),
						'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/file-icon-root.png"
						);
		}
			
			try{
				$files = $this->xcloner_file_system->list_directory($folder);
			}catch(Exception $e){
				
				print $e->getMessage();
				$this->logger->error($e->getMessage());

				return;
			}
			
			$type = array();
			foreach ($files as $key => $row)
			{
				$type[$key] = $row['type'];
			}
			array_multisort($type, SORT_ASC, $files);
			
			foreach($files as $file)
			{
				$children = false;
				$text = $file['basename'];
				
				if($file['type'] == "dir")
					$children = true;
				else
					 $text .= " (". $this->xcloner_requirements->file_format_size($file['size']).")";
				
				if(in_array($file['path'], $this->exclude_files_by_default))
					$selected = true;
				else
					$selected = false;
					
				$data[] = array(
							'id' => $file['path'],
							'parent' => $folder,
							'text' => $text,
							'children' => $children,
							'state' =>  array('selected' => $selected, 'opened' => false),
							'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/file-icon-".strtolower(substr($file['type'], 0, 1)).".png"
							);
			}
		
		
		return $this->send_response($data);
	}
	
	public function get_database_tables_action()
	{
		if (!current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
		
		$this->init_db();
		
		$database = $this->xcloner_sanitization->sanitize_input_as_raw($_POST['id']);
		
		$data = array();
		
		$xcloner_backup_only_wp_tables = $this->xcloner_settings->get_xcloner_option('xcloner_backup_only_wp_tables');
	
		if($database == "#")
		{
			try{
				$return = $this->db_link->get_all_databases();
			}catch(Exception $e){
				$this->logger->error($e->getMessage());
			}
			
			foreach($return as $database)
			{
				if($xcloner_backup_only_wp_tables and $database['name'] != $this->xcloner_settings->get_db_database())
					continue;
					
				$state = array();
				
				if($database['name'] == $this->xcloner_settings->get_db_database())
					$state = array('selected' => 'false', 'opened' => 'false');
					
				$data[] = array(
						'id' => $database['name'],
						'parent' => '#',
						'text' => $database['name']." (".(int)$database['num_tables'].")",
						'children' => true,
						'state' => $state,
						'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/database-icon.png"
						);
			}
			
		}
		
		else{
			
			try{
				$return = $this->db_link->listTables($database, "", 1);
			}catch(Exception $e){
				$this->logger->error($e->getMessage());
			}
			
			foreach($return as $table)
			{
				$state = array();
				
				if($xcloner_backup_only_wp_tables and !stristr($table['name'], $this->xcloner_settings->get_table_prefix()))
					continue;
				
				if(isset($database['name']) and $database['name'] == $this->xcloner_settings->get_db_database())
					$state = array('selected' => 'false');
					
				$data[] = array(
						'id' => $table['name'],
						'parent' => $database,
						'text' => $table['name']." (".(int)$table['records'].")",
						'children' => false,
						'state' => $state,
						'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/table-icon.png"
						);
			}
		}
		
		return $this->send_response($data);
	}
	
	private function send_response($data)
	{
		if( ob_get_length() )
			ob_clean();
		wp_send_json($data);
		
		die();
	}
}
