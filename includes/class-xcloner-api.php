<?php

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
//use League\Flysystem\Util\ContentListingFormatter;
use League\Flysystem\Adapter\Local;

class Xcloner_Api{

	private $xcloner_database;
	private $xcloner_settings;
	private $xcloner_file_system;
	private $xcloner_requirements;
	private $_file_system;
	private $archive_system;
	//protected $exclude_files_by_default = array("administrator/backups", "wp-content/backups");
	private $form_params;
	private $logger;
	
	public function __construct()
	{
		global $wpdb;
		
		$wpdb->show_errors		= false;
		
		$this->xcloner_settings 		= new Xcloner_Settings();
		
		//generating the hash suffix for tmp xcloner store folder
		if(isset($_POST['hash'])){
			if($_POST['hash'] == "generate_hash")
				$this->xcloner_settings->generate_new_hash();
			else
				$this->xcloner_settings->set_hash($_POST['hash']);
		}
		
		$this->xcloner_file_system 		= new Xcloner_File_System($this->xcloner_settings->get_hash());
		$this->xcloner_sanitization 	= new Xcloner_Sanitization();
		$this->xcloner_requirements 	= new XCloner_Requirements();
		$this->archive_system 			= new Xcloner_Archive($this->xcloner_settings->get_hash());
		$this->xcloner_database 		= new XCloner_Database($this->xcloner_settings->get_hash());
		$this->logger 					= new XCloner_Logger("xcloner_api", $this->xcloner_settings->get_hash());
		
	}
	
	public function init_db()
	{
		return;
		
		
		$data['dbHostname'] = $this->xcloner_settings->get_db_hostname();
		$data['dbUsername'] = $this->xcloner_settings->get_db_username();
		$data['dbPassword'] = $this->xcloner_settings->get_db_password();
		$data['dbDatabase'] = $this->xcloner_settings->get_db_database();
		
		
		$data['recordsPerSession'] 		= $this->xcloner_settings->get_xcloner_option('xcloner_database_records_per_request');
		$data['TEMP_DBPROCESS_FILE'] 	= $this->xcloner_settings->get_xcloner_tmp_path().DS.".database";
		$data['TEMP_DUMP_FILE'] 		= $this->xcloner_settings->get_xcloner_tmp_path().DS."database-sql.sql";
		
		try
		{
			//$xcloner_db = new XCloner_Database($data['dbUsername'], $data['dbPassword'], $data['dbDatabase'], $data['dbHostname']);
			$this->xcloner_database->init($data);

		}catch(Exception $e)
		{
			$this->send_response($e->getMessage());
			$this->logger->error($e->getMessage());
			
		}
		
		return $this->xcloner_database;
		
	
	}
	
	public function save_schedule()
	{
		global $wpdb; 
		
		if (!current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
		
		$scheduler = new Xcloner_Scheduler();
		$params = array();
		$schedule = array();
		
		if(isset($_POST['data']))
			$params = json_decode(stripslashes($_POST['data']));
		
		$this->process_params($params);
		
		
		if(isset($_POST['id']))
		{
			
			$this->form_params['backup_params']['backup_name'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['backup_name']);
			$this->form_params['backup_params']['email_notification'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['email_notification']);
			$this->form_params['backup_params']['schedule_name'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['schedule_name']);
			$this->form_params['backup_params']['start_at'] = strtotime($_POST['schedule_start_date']);
			$this->form_params['backup_params']['schedule_frequency'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['schedule_frequency']);
			$this->form_params['database'] = json_decode(stripslashes($this->xcloner_sanitization->sanitize_input_as_raw($_POST['table_params'])));
			$this->form_params['excluded_files'] = json_decode(stripslashes($this->xcloner_sanitization->sanitize_input_as_raw($_POST['excluded_files'])));
			
			$schedule['start_at'] = $this->form_params['backup_params']['start_at'] ;
			
			if(!isset($_POST['status']))
				$schedule['status'] = 0;
			else	
				$schedule['status'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['status']);
				
			//$params = json_decode(stripslashes($this->form_params['backup_params']['params']));
		}else{
		
			$schedule['status'] = 1;
			$schedule['start_at'] = strtotime($this->form_params['backup_params']['schedule_start_date'] .
								" ".$this->form_params['backup_params']['schedule_start_time']);
		}
		
		if(!$schedule['start_at'])						
			$schedule['start_at'] = time();
		
		$schedule['start_at'] = date('Y-m-d H:i:s', $schedule['start_at']);	
		
		$schedule['name'] = $this->form_params['backup_params']['schedule_name'];
		$schedule['recurrence'] = $this->form_params['backup_params']['schedule_frequency'];
		
		$schedule['params'] = json_encode($this->form_params);
		
		#print_r($_POST);
		//print_r($schedule);exit;
		
		if(!isset($_POST['id']))
		{
			$insert = $wpdb->insert( 
				$wpdb->prefix.'xcloner_scheduler', 
				$schedule, 
				array( 
					'%s', 
					'%s' 
				) 
			);
		}else		{
			$insert = $wpdb->update( 
				$wpdb->prefix.'xcloner_scheduler', 
				$schedule, 
				array( 'id' => $_POST['id'] ), 
				array( 
					'%s', 
					'%s' 
				) 
			);
		}
		if(isset($_POST['id']))
			$scheduler->update_cron_hook($_POST['id']);
			
		if( $wpdb->last_error ) {
            $response['error'] = 1;
            $response['error_message'] = $wpdb->last_error/*."--".$wpdb->last_query*/;
            
        }
        
        $scheduler->update_wp_cron_hooks();
		$response['finished'] = 1;
		
		$this->send_response($response);
	}
	
	public function backup_files()
	{
		if (!current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
		
		$params = json_decode(stripslashes($_POST['data']));
		
		$init 	= (int)$_POST['init'];
		
		if($params === NULL)
			 die( '{"status":false,"msg":"The post_data parameter must be valid JSON"}' );
			 
		$this->process_params($params);
		
		$return['finished'] = 1;

		$return = $this->archive_system->start_incremental_backup($this->form_params['backup_params'], $this->form_params['extra'], $init);
		
		$data = $return;
		
		if($return['finished'] )
			$this->xcloner_file_system->remove_tmp_filesystem();
		
		return $this->send_response($data, $hash = 1);
	}
	
	public function backup_database()
	{
		if (!current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
		
		$params = json_decode(stripslashes($_POST['data']));
		
		$init 	= (int)$_POST['init'];
		
		if($params === NULL)
			 die( '{"status":false,"msg":"The post_data parameter must be valid JSON"}' );
		
		$this->process_params($params);
			
		//$xcloner_database = $this->init_db();	
		$return = $this->xcloner_database->start_database_recursion($this->form_params['database'], $this->form_params['extra'], $init);
		
		if(isset($return['error']) and $return['error'])
			$data['finished'] = 1;
		else	
			$data['finished'] = $return['finished'];
			
		$data['extra'] = $return;
		
		return $this->send_response($data, $hash = 1);
	}
	
	public function scan_filesystem()
	{
		if (!current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
		
		$params = json_decode(stripslashes($_POST['data']));
		$init 	= (int)$_POST['init'];
		
		if($params === NULL)
			 die( '{"status":false,"msg":"The post_data parameter must be valid JSON"}' );
			 
		$hash = $this->process_params($params);
		
		$this->xcloner_file_system->set_excluded_files($this->form_params['excluded_files']);
		
		$return = $this->xcloner_file_system->start_file_recursion($init);
		
		$data["finished"] = !$return;
		$data["total_files_num"] = $this->xcloner_file_system->get_scanned_files_num();
		$data["last_logged_file"] = $this->xcloner_file_system->last_logged_file();
		$data["total_files_size"] = number_format($this->xcloner_file_system->get_scanned_files_total_size()/(1024*1024), 2);
		
		return $this->send_response($data, $hash = 1);
	}
	
	private function process_params($params)
	{
		if(isset($params->hash))
			$this->xcloner_settings->set_hash($params->hash);
			
		$this->form_params['extra'] = array();
		$this->form_params['backup_params'] = array();
		
		$this->form_params['database'] = array();
		
		if(isset($params->backup_params))
		{
			foreach($params->backup_params as $param)
			{
				$this->form_params['backup_params'][$param->name] = $this->xcloner_sanitization->sanitize_input_as_string($param->value);
				$this->logger->debug("Adding form parameter ".$param->name.".".$param->value."\n", array('POST', 'fields filter'));
			}
		}
		
		$this->form_params['database'] = array();
		
		if(isset($params->table_params))
		{
			foreach($params->table_params as $param)
			{
				$this->form_params['database'][$param->parent][] = $this->xcloner_sanitization->sanitize_input_as_raw($param->id);
				$this->logger->debug("Adding database filter ".$param->parent.".".$param->id."\n", array('POST', 'database filter'));
			}
		}
		
		$this->form_params['excluded_files'] =  array();
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
					$this->logger->debug("Adding file filter ".$param->id."\n", array('POST', 'exclude files filter'));
				}
			}
			$this->form_params['excluded_files'] = (array)$unique_exclude_files;
			
		}
		
		//$this->form_params['excluded_files'] =  array_merge($this->form_params['excluded_files'], $this->exclude_files_by_default);
		
		if(isset($params->extra))
		{
			foreach($params->extra as $key=>$value)
				$this->form_params['extra'][$key] = $this->xcloner_sanitization->sanitize_input_as_raw($value);
		}
			
		return $this->xcloner_settings->get_hash();
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
				
				if(in_array($file['path'], $this->xcloner_file_system->get_excluded_files()))
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
		
		
		return $this->send_response($data, 0);
	}
	
	public function get_database_tables_action()
	{
		if (!current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
		
		$database = $this->xcloner_sanitization->sanitize_input_as_raw($_POST['id']);
		
		$data = array();
		
		$xcloner_backup_only_wp_tables = $this->xcloner_settings->get_xcloner_option('xcloner_backup_only_wp_tables');
	
		if($database == "#")
		{
			try{
				$return = $this->xcloner_database->get_all_databases();
			}catch(Exception $e){
				$this->logger->error($e->getMessage());
			}
			
			foreach($return as $database)
			{
				if($xcloner_backup_only_wp_tables and $database['name'] != $this->xcloner_settings->get_db_database())
					continue;
					
				$state = array();
				
				if($database['name'] == $this->xcloner_settings->get_db_database())
				{
					$state['selected'] = true;
					if($database['num_tables'] < 25)
						$state['opened'] = false;
				}
					
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
				$return = $this->xcloner_database->list_tables($database, "", 1);
			}catch(Exception $e){
				$this->logger->error($e->getMessage());
			}
			
			foreach($return as $table)
			{
				$state = array();
				
				if($xcloner_backup_only_wp_tables and !stristr($table['name'], $this->xcloner_settings->get_table_prefix()))
					continue;
				
				if(isset($database['name']) and $database['name'] == $this->xcloner_settings->get_db_database())
					$state = array('selected' => true);
					
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
		
		return $this->send_response($data, 0);
	}
	
	public function get_schedule_by_id()
	{
		$schedule_id = $this->xcloner_sanitization->sanitize_input_as_int($_GET['id']);
		$scheduler = new Xcloner_Scheduler();
		$data  = $scheduler->get_schedule_by_id($schedule_id);
		
		return $this->send_response($data);
	}
	
	public function get_scheduler_list()
	{
		$scheduler = new Xcloner_Scheduler();
		$data  = $scheduler->get_scheduler_list();
		
		foreach($data as $res)
		{
			$action = "<a href=\"#".$res->id."\" class=\"edit\" title='Edit'> <i class=\"material-icons \">edit</i></a> 
					<a href=\"#".$res->id."\" class=\"delete\" title='Delete'><i class=\"material-icons  \">delete</i></a>";
			if($res->status)
				$status = '<i class="material-icons active status">timer</i>';
			else
				$status = '<i class="material-icons status inactive">timer_off</i>';
				
			$next_run_time = wp_next_scheduled('xcloner_scheduler_'.$res->id, array($res->id));
				
			$next_run = date("d M, Y H:i", $next_run_time);	
			
			if(!$next_run_time >= time())
				$next_run = " ";
			
			if(trim($next_run))
			{
				$date_text = $next_run;
				
				if($next_run_time >= time())
					$next_run = "in ".human_time_diff($next_run_time, time());
				else
					$next_run = __("executed", "xcloner");
				
				$next_run .=" ($date_text)";	
			}
				
			$return['data'][] = array($res->id, $res->name, $res->recurrence,/*$res->start_at,*/ $next_run, $status, $action);
		}
		
		return $this->send_response($return, 0);
	}
	
	public function delete_schedule_by_id()
	{
		$schedule_id = $this->xcloner_sanitization->sanitize_input_as_int($_GET['id']);
		$scheduler = new Xcloner_Scheduler();
		$data['finished']  = $scheduler->delete_schedule_by_id($schedule_id);
		
		return $this->send_response($data);
	}
	
	private function send_response($data, $attach_hash = 1)
	{
		if($attach_hash and null !== $this->xcloner_settings->get_hash())
		{
			$data['hash'] = $this->xcloner_settings->get_hash();
		}
			
		if( ob_get_length() )
			ob_clean();
		wp_send_json($data);
		
		die();
	}
}
