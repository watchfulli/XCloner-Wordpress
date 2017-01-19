<?php

class Xcloner_Api{

	var $db_link;
	var $xcloner_settings;
	
	public function __construct()
	{
	}
	
	public function init_db()
	{
		$xcloner_db = new XCloner_Database();
		$xcloner_settings = new Xcloner_Settings();
		
		$data['dbHostname'] = $xcloner_settings->get_db_hostname();
		$data['dbUsername'] = $xcloner_settings->get_db_username();
		$data['dbPassword'] = $xcloner_settings->get_db_password();
		$data['dbDatabase'] = $xcloner_settings->get_db_database();
		
		$data['recordsPerSession'] = $xcloner_settings->get_xcloner_option('xcloner_database_records_per_request');
		
		$xcloner_db->init($data);
		
		$this->db_link = $xcloner_db;
		$this->xcloner_settings =$xcloner_settings;
	
	}
	public function get_database_tables_action()
	{
		$this->init_db();
		
		$database = Xcloner_Sanitization::sanitize_input_as_raw($_POST['id']);
		
		
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
						'text' => $database['name'],
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
				
				if($database['name'] == $this->xcloner_settings->get_db_database())
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
