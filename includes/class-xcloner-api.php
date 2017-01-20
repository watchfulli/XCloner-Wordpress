<?php

class Xcloner_Api{

	var $db_link;
	var $xcloner_settings;
	var $xcloner_file_system;
	var $xcloner_requirements;
	
	public function __construct()
	{
		$this->xcloner_settings 		= new Xcloner_Settings();
		$this->xcloner_file_system 		= new Xcloner_File_System();
		$this->xcloner_sanitization 	= new Xcloner_Sanitization();
		$this->xcloner_requirements 	= new XCloner_Requirements();
		
		
	}
	
	public function init_db()
	{
		$xcloner_db = new XCloner_Database();
		
		
		$data['dbHostname'] = $this->xcloner_settings->get_db_hostname();
		$data['dbUsername'] = $this->xcloner_settings->get_db_username();
		$data['dbPassword'] = $this->xcloner_settings->get_db_password();
		$data['dbDatabase'] = $this->xcloner_settings->get_db_database();
		
		$data['recordsPerSession'] = $this->xcloner_settings->get_xcloner_option('xcloner_database_records_per_request');
		
		$xcloner_db->init($data);
		
		$this->db_link = $xcloner_db;
		
	
	}
	
	public function get_file_system_action()
	{
		if (!current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
		
		$folder = $this->xcloner_sanitization->sanitize_input_as_relative_path($_POST['id']);
		$data = array();
		
		if($folder == "#"){
			
			$folder = "$";
			$list_directory = $this->xcloner_settings->get_xcloner_start_path();
			$data[] = array(
						'id' => $folder,
						'parent' => '#',
						'text' => $list_directory,
						//'children' => true,
						'state' =>  array('selected' => false, 'opened' => true),
						'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/file-icon-root.png"
						);
		}
		else
			$list_directory = $this->xcloner_settings->get_xcloner_dir_path(substr($folder, 1, strlen($folder)));
		
			$files = $this->xcloner_file_system->list_directory($list_directory);
		
			foreach($files as $file)
			{
				$children = false;
				$text = $file['filename'];
				
				if($file['type'] == "D")
					$children = true;
				else
					 $text .= " (". $this->xcloner_requirements->file_format_size($file['size']).")";
					
				$data[] = array(
							'id' => $folder.DS.$file['filename'],
							'parent' => $folder,
							'text' => $text,
							'children' => $children,
							'state' =>  array('selected' => false, 'opened' => false),
							'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/file-icon-".$file['type'].".png"
							);
			}
		
		//print_r($data);exit;
		
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
			$return = $this->db_link->get_all_databases();
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
			
			$return = $this->db_link->listTables($database, "", 1);
			
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
	
	public function send_response($data)
	{
		if( ob_get_length() )
			ob_clean();
		wp_send_json($data);
		
		die();
	}
}
