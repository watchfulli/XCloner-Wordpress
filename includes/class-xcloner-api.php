<?php

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
use League\Flysystem\Adapter\Local;

use splitbrain\PHPArchive\Tar;
use splitbrain\PHPArchive\Zip;
use splitbrain\PHPArchive\Archive;
use splitbrain\PHPArchive\FileInfo;


class Xcloner_Api{

	private $xcloner_database;
	private $xcloner_settings;
	private $xcloner_file_system;
	private $xcloner_requirements;
	private $xcloner_sanitization;
	private $archive_system;
	private $form_params;
	private $logger;
	private $xcloner_container;
	
	public function __construct(Xcloner $xcloner_container)
	{
		global $wpdb;
		
		if(WP_DEBUG)
		{
			error_reporting(0);
		}
		
		if( ob_get_length() )
			ob_end_clean();
		ob_start();
		
		$wpdb->show_errors			= false;
			
		$this->xcloner_container	= $xcloner_container;
				
		$this->xcloner_settings 	= $xcloner_container->get_xcloner_settings();
		$this->logger				= $xcloner_container->get_xcloner_logger()->withName("xcloner_api");
		$this->xcloner_file_system 	= $xcloner_container->get_xcloner_filesystem();
		$this->xcloner_sanitization = $xcloner_container->get_xcloner_sanitization();
		$this->xcloner_requirements = $xcloner_container->get_xcloner_requirements();
		$this->archive_system 		=  $xcloner_container->get_archive_system();
		$this->xcloner_database 	=  $xcloner_container->get_xcloner_database();
		$this->xcloner_scheduler 	=  $xcloner_container->get_xcloner_scheduler();
		
		if(isset($_POST['API_ID'])){
			$this->logger->info("Processing ajax request ID ".substr($this->xcloner_sanitization->sanitize_input_as_string($_POST['API_ID']), 0 , 15));
		}
		
	}
	
	public function get_xcloner_container()
	{
		return $this->xcloner_container;
	}
	
	private function check_access()
	{
		if (function_exists('current_user_can') && !current_user_can('manage_options')) {
			die("Not allowed access here!");
		}
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
			$this->xcloner_database->init($data);

		}catch(Exception $e){
			
			$this->send_response($e->getMessage());
			$this->logger->error($e->getMessage());
			
		}
		
		return $this->xcloner_database;
		
	
	}
	
	/*
	 * 
	 * Save Schedule API
	 */
	public function save_schedule()
	{
		global $wpdb; 
		
		$this->check_access();
		
		$scheduler = $this->xcloner_scheduler;
		$params = array();
		$schedule = array();
		$response = array();
		
		if(isset($_POST['data']))
			$params = json_decode(stripslashes($_POST['data']));
		
		$this->process_params($params);
		
		if(isset($_POST['id']))
		{
			
			$this->form_params['backup_params']['backup_name'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['backup_name']);
			$this->form_params['backup_params']['email_notification'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['email_notification']);
			if($_POST['diff_start_date']){
				$this->form_params['backup_params']['diff_start_date'] = strtotime($this->xcloner_sanitization->sanitize_input_as_string($_POST['diff_start_date']));
			}else{
				$this->form_params['backup_params']['diff_start_date'] = "";
				}
			$this->form_params['backup_params']['schedule_name'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['schedule_name']);
			$this->form_params['backup_params']['start_at'] = strtotime($_POST['schedule_start_date']);
			$this->form_params['backup_params']['schedule_frequency'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['schedule_frequency']);
			$this->form_params['backup_params']['schedule_storage'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['schedule_storage']);
			$this->form_params['database'] = (stripslashes($this->xcloner_sanitization->sanitize_input_as_raw($_POST['table_params'])));
			$this->form_params['excluded_files'] = (stripslashes($this->xcloner_sanitization->sanitize_input_as_raw($_POST['excluded_files'])));
			
			//$this->form_params['backup_params']['backup_type'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['backup_type']);
			
			$tables = explode(PHP_EOL, $this->form_params['database']);
			$return = array();
			
			foreach($tables as $table)
			{
					$table = str_replace("\r","", $table);
					$data = explode(".", $table);
					if(isset($data[1]))
						$return[$data[0]][] = $data[1];
			}
			
			$this->form_params['database'] = ($return);
			
			$excluded_files = explode(PHP_EOL, $this->form_params['excluded_files']);
			$return = array();
			
			foreach($excluded_files as $file)
			{
				$file = str_replace("\r","", $file);
				if($file)
					$return[] = $file;
			}
			
			$this->form_params['excluded_files'] = ($return);
			
			$schedule['start_at'] = $this->form_params['backup_params']['start_at'];
			
			if(!isset($_POST['status']))
				$schedule['status'] = 0;
			else	
				$schedule['status'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['status']);
		}else{
		
			$schedule['status'] = 1;
			$schedule['start_at'] = strtotime($this->form_params['backup_params']['schedule_start_date'] .
								" ".$this->form_params['backup_params']['schedule_start_time']);
			
			if($schedule['start_at'] <= time())
			{
				$schedule['start_at'] = "";
			}
		}
		
		if(!$schedule['start_at'])						
		{
			$schedule['start_at'] = date('Y-m-d H:i:s', time());
		}else{
			$schedule['start_at'] = date('Y-m-d H:i:s', $schedule['start_at'] - (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS) );	
		}
		
		$schedule['name'] = $this->form_params['backup_params']['schedule_name'];
		$schedule['recurrence'] = $this->form_params['backup_params']['schedule_frequency'];
		if(!isset($this->form_params['backup_params']['schedule_storage']))
		{
			$this->form_params['backup_params']['schedule_storage'] = "";
		}
		$schedule['remote_storage'] = $this->form_params['backup_params']['schedule_storage'];
		//$schedule['backup_type'] = $this->form_params['backup_params']['backup_type'];
		$schedule['params'] = json_encode($this->form_params);

		if(!isset($_POST['id']))
		{
			$wpdb->insert( 
				$wpdb->prefix.'xcloner_scheduler', 
				$schedule, 
				array( 
					'%s', 
					'%s' 
				) 
			);
		}else		{
			$wpdb->update( 
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
		{
			$scheduler->update_cron_hook($_POST['id']);
		}
			
		if( $wpdb->last_error ) {
            $response['error'] = 1;
            $response['error_message'] = $wpdb->last_error/*."--".$wpdb->last_query*/;
            
        }
        
        $scheduler->update_wp_cron_hooks();
		$response['finished'] = 1;
		
		$this->send_response($response);
	}
	
	/*
	 * 
	 * Backup Files API
	 * 
	 */ 
	public function backup_files()
	{
		$this->check_access();
		
		$params = json_decode(stripslashes($_POST['data']));
		
		$init 	= (int)$_POST['init'];
		
		if($params === NULL)
			 die( '{"status":false,"msg":"The post_data parameter must be valid JSON"}' );
			 
		$this->process_params($params);
		
		$return['finished'] = 1;

		//$return = $this->archive_system->start_incremental_backup($this->form_params['backup_params'], $this->form_params['extra'], $init);
		try{
			$return = $this->archive_system->start_incremental_backup($this->form_params['backup_params'], $this->form_params['extra'], $init);
		}catch(Exception $e)
		{
			$return = array();
			$return['error'] = true;
			$return['status'] = 500;
			$return['error_message'] = $e->getMessage();
			return $this->send_response($return, $hash = 1);
		}
		
		if($return['finished'])
		{
			$return['extra']['backup_parent'] = $this->archive_system->get_archive_name_with_extension();
			if($this->xcloner_file_system->is_part($this->archive_system->get_archive_name_with_extension()))
				$return['extra']['backup_parent'] = $this->archive_system->get_archive_name_multipart();
		}
		
		$data = $return;
		
		//check if backup is finished
		if($return['finished'] )
		{
			if(isset($this->form_params['backup_params']['email_notification']) and $to=$this->form_params['backup_params']['email_notification'])
			{
				try{
					$from = "";
					$subject = "";
					$additional['lines_total'] = $return['extra']['lines_total'];
					$this->archive_system->send_notification($to, $from, $subject, $return['extra']['backup_parent'], $this->form_params,"", $additional);
				}catch(Exception $e)
				{
					$this->logger->error($e->getMessage());
				}
			}
			$this->xcloner_file_system->remove_tmp_filesystem();
		}
		
		return $this->send_response($data, $hash = 1);
	}
	
	/*
	 * 
	 * Backup Database API
	 * 
	 */ 
	public function backup_database()
	{
		$this->check_access();
		
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
	
	/*
	 * 
	 * Scan Filesystem API
	 * 
	 */ 
	public function scan_filesystem()
	{
		$this->check_access();
		
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
		$data["total_files_size"] = sprintf("%.2f",$this->xcloner_file_system->get_scanned_files_total_size()/(1024*1024));
		
		return $this->send_response($data, $hash = 1);
	}
	
	/*
	 * 
	 * Process params sent by the user
	 * 
	 */ 
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
		
		if(isset($this->form_params['backup_params']['diff_start_date']) and $this->form_params['backup_params']['diff_start_date'])
		{
			$this->form_params['backup_params']['diff_start_date'] = strtotime($this->form_params['backup_params']['diff_start_date']);
			$this->xcloner_file_system->set_diff_timestamp_start($this->form_params['backup_params']['diff_start_date']);
		}
		
		return $this->xcloner_settings->get_hash();
	}
	
	/*
	 * 
	 * Get file list for tree view API
	 * 
	 */ 
	public function get_file_system_action()
	{
		$this->check_access();
		
		$folder = $this->xcloner_sanitization->sanitize_input_as_relative_path($_POST['id']);
		
		$data = array();
		
		if($folder == "#"){
			
			$folder = "/";
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
				
				if($this->xcloner_file_system->is_excluded($file))
					$selected = true;
				else
					$selected = false;
					
				$data[] = array(
							'id' => $file['path'],
							'parent' => $folder,
							'text' => $text,
							//'title' => "test",
							'children' => $children,
							'state' =>  array('selected' => $selected, 'opened' => false, "checkbox_disabled" => $selected),
							'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/file-icon-".strtolower(substr($file['type'], 0, 1)).".png"
							);
			}
		
		
		return $this->send_response($data, 0);
	}
	
	/*
	 * 
	 * Get databases/tables list for frontend tree display API
	 * 
	 */ 
	public function get_database_tables_action()
	{
		$this->check_access();
		
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
	
	/*
	 * 
	 * Get schedule by id API
	 * 
	 */ 
	public function get_schedule_by_id()
	{
		$this->check_access();
		
		$schedule_id = $this->xcloner_sanitization->sanitize_input_as_int($_GET['id']);
		$scheduler = $this->xcloner_scheduler;
		$data  = $scheduler->get_schedule_by_id($schedule_id);
		
		$data['start_at'] = date("Y-m-d H:i", strtotime($data['start_at']) + (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS));
		if(isset($data['backup_params']->diff_start_date) && $data['backup_params']->diff_start_date != "")
		{
			$data['backup_params']->diff_start_date = date("Y-m-d", ($data['backup_params']->diff_start_date) );
		}
		
		return $this->send_response($data);
	}
	
	/*
	 * 
	 * Get Schedule list API
	 * 
	 */ 
	public function get_scheduler_list()
	{
		$this->check_access();
		
		$scheduler = $this->xcloner_scheduler;
		$data  = $scheduler->get_scheduler_list();
		$return['data'] = array();
		
		foreach($data as $res)
		{
			$action = "<a href=\"#".$res->id."\" class=\"edit\" title='Edit'> <i class=\"material-icons \">edit</i></a> 
					<a href=\"#".$res->id."\" class=\"delete\" title='Delete'><i class=\"material-icons  \">delete</i></a>";
			if($res->status)
				$status = '<i class="material-icons active status">timer</i>';
			else
				$status = '<i class="material-icons status inactive">timer_off</i>';
				
			$next_run_time = wp_next_scheduled('xcloner_scheduler_'.$res->id, array($res->id));
				
			$next_run = date(get_option('date_format')." ".get_option('time_format'), $next_run_time);	
			
			$remote_storage = $res->remote_storage;
			
			if(!$next_run_time >= time())
				$next_run = " ";
			
			if(trim($next_run))
			{
				$date_text = date(get_option('date_format')." ".get_option('time_format'), $next_run_time + (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS));
				
				if($next_run_time >= time())
					$next_run = "in ".human_time_diff($next_run_time, time());
				else
					$next_run = __("executed", 'xcloner-backup-and-restore');
				
				$next_run = "<a href='#' title='".$date_text."'>".$next_run."</a>";
				//$next_run .=" ($date_text)";	
			}
			
			$backup_text = "";
			$backup_size = "";
			$backup_time = "";
			
			if($res->last_backup)
			{
				if( $this->xcloner_file_system->get_storage_filesystem()->has($res->last_backup))
				{
					$metadata = $this->xcloner_file_system->get_storage_filesystem()->getMetadata($res->last_backup);
					$backup_size  = size_format($this->xcloner_file_system->get_backup_size($res->last_backup));
					$backup_time  = date(get_option('date_format')." ".get_option('time_format'), $metadata['timestamp']+(get_option( 'gmt_offset' ) * HOUR_IN_SECONDS));
				}
			
				$backup_text = "<span title='".$backup_time."' class='shorten_string'>".$res->last_backup." (".$backup_size.")</span>";
			}
			
			$schedules = wp_get_schedules();

			if(isset($schedules[$res->recurrence]))
			{
				$res->recurrence = $schedules[$res->recurrence]['display'];
			}
				
			$return['data'][] = array($res->id, $res->name, $res->recurrence,/*$res->start_at,*/ $next_run, $remote_storage, $backup_text, $status, $action);
		}

		return $this->send_response($return, 0);
	}
	
	/*
	 * 
	 * Delete Schedule by ID API
	 * 
	 */
	public function delete_schedule_by_id()
	{
		$this->check_access();
		
		$schedule_id = $this->xcloner_sanitization->sanitize_input_as_int($_GET['id']);
		$scheduler = $this->xcloner_scheduler;
		$data['finished']  = $scheduler->delete_schedule_by_id($schedule_id);
		
		return $this->send_response($data);
	}
	
	/*
	 * 
	 * Delete backup by name from the storage path
	 * 
	 */ 
	public function delete_backup_by_name()
	{
		$this->check_access();
		
		$backup_name = $this->xcloner_sanitization->sanitize_input_as_string($_POST['name']);
		$storage_selection = $this->xcloner_sanitization->sanitize_input_as_string($_POST['storage_selection']);
		
		$data['finished']  = $this->xcloner_file_system->delete_backup_by_name($backup_name, $storage_selection);
		
		return $this->send_response($data);
	}
	
	public function list_backup_files()
	{
		$this->check_access();
		
		$backup_parts = array();
		
		$source_backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
		$start = $this->xcloner_sanitization->sanitize_input_as_int($_POST['start']);
		$return['part'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['part']);
		
		$backup_file = $source_backup_file;
		
		if($this->xcloner_file_system->is_multipart($backup_file))
		{
			$backup_parts = $this->xcloner_file_system->get_multipart_files($backup_file);
			$backup_file = $backup_parts[$return['part']];
		}
		
		try{
			$tar = new Tar();
			$tar->open($this->xcloner_settings->get_xcloner_store_path().DS.$backup_file, $start);
		
			$data = $tar->contents(get_option('xcloner_files_to_process_per_request'));
		}catch(Exception $e)
		{
			$return['error'] = true;
			$return['message'] = $e->getMessage();
			$this->send_response($return, 0);
		}
		
		$return['files'] 		= array();
		$return['finished'] 	= 1;
		$return['total_size'] 	= filesize($this->xcloner_settings->get_xcloner_store_path().DS.$backup_file);
		$i = 0;
		
		if(isset($data['extracted_files']) and is_array($data['extracted_files']))
		{
			foreach($data['extracted_files'] as $file)
			{
				$return['files'][$i]['path'] = $file->getPath();
				$return['files'][$i]['size'] = $file->getSize();
				$return['files'][$i]['mtime'] = date(get_option('date_format')." ".get_option('time_format'), $file->getMtime());
				
				$i++;
			}
		}
		
		if(isset($data['start']))
		{
			$return['start'] = $data['start'];
			$return['finished'] = 0;	
		}else{
			if($this->xcloner_file_system->is_multipart($source_backup_file))
			{
				$return['start'] = 0;
				
				++$return['part'];
			
				if($return['part'] < sizeof($backup_parts))	
					$return['finished'] = 0;
				
			}
		}	
		
		$this->send_response($return, 0);
	}
	
	public function copy_backup_remote_to_local()
	{
	
		$this->check_access();
		
		$backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
		$storage_type = $this->xcloner_sanitization->sanitize_input_as_string($_POST['storage_type']);
		
		$xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
		
		$return = array();
		
		try
		{
			if(method_exists($xcloner_remote_storage, "copy_backup_remote_to_local"))
			{
				$return = call_user_func_array(array($xcloner_remote_storage, "copy_backup_remote_to_local"), array($backup_file, $storage_type));
			}
		}catch(Exception $e){
		
			$return['error'] = 1;
			$return['message'] = $e->getMessage();
		}
		
		if(!$return)
		{
			$return['error'] = 1;
			$return['message'] = "Upload failed, please check the error log for more information!";
		}
			
		
		$this->send_response($return, 0);
		
	}
	
	/*
	 * 
	 * Upload backup to remote API
	 * 
	 */ 
	public function upload_backup_to_remote()
	{
		$this->check_access();
		
		$backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
		$storage_type = $this->xcloner_sanitization->sanitize_input_as_string($_POST['storage_type']);
		
		$xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
		
		$return = array();
		
		try
		{
			if(method_exists($xcloner_remote_storage, "upload_backup_to_storage"))
			{
				$return = call_user_func_array(array($xcloner_remote_storage, "upload_backup_to_storage"), array($backup_file, $storage_type));
			}
		}catch(Exception $e){
		
			$return['error'] = 1;
			$return['message'] = $e->getMessage();
		}
		
		if(!$return)
		{
			$return['error'] = 1;
			$return['message'] = "Upload failed, please check the error log for more information!";
		}
			
		
		$this->send_response($return, 0);
		
	}
	
	/*
	 * 
	 * Remote Storage Status Save
	 * 
	 */ 
	public function remote_storage_save_status()
	{
		$this->check_access();
		
		$xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
		
		$return['finished'] = $xcloner_remote_storage->change_storage_status($_POST['id'], $_POST['value']);
		
		$this->send_response($return, 0);
	}
	
	
	public function download_restore_script()
	{
		$this->check_access();
		
		@ob_end_clean();
		
		$adapter = new Local(dirname(__DIR__) ,LOCK_EX, 'SKIP_LINKS');
		$xcloner_plugin_filesystem = new Filesystem($adapter, new Config([
				'disable_asserts' => true,
			]));
		
		/* Generate PHAR FILE
		$file = 'restore/vendor.built';	

		if(file_exists($file))
			unlink($file);
		$phar2 = new Phar($file, 0, 'vendor.phar');
		
		// add all files in the project, only include php files
		$phar2->buildFromIterator(
		    new RecursiveIteratorIterator(
		     new RecursiveDirectoryIterator(__DIR__.'/vendor/')),
		    __DIR__);
		
		$phar2->setStub($phar2->createDefaultStub('vendor/autoload.php', 'vendor/autoload.php')); 
		 * */
			
		$tmp_file = $this->xcloner_settings->get_xcloner_tmp_path().DS."xcloner-restore.tgz";
		
		$tar = new Tar();
		$tar->create($tmp_file);
		
		$tar->addFile(dirname(__DIR__)."/restore/vendor.build.txt", "vendor.phar");
		//$tar->addFile(dirname(__DIR__)."/restore/vendor.tgz", "vendor.tgz");
		
		$files = $xcloner_plugin_filesystem->listContents("vendor/", true);
		foreach($files as $file)
		{
			$tar->addFile(dirname(__DIR__).DS.$file['path'], $file['path']);
		}
		
		$content = file_get_contents(dirname(__DIR__)."/restore/xcloner_restore.php");
		$content = str_replace("define('AUTH_KEY', '');", "define('AUTH_KEY', '".md5(AUTH_KEY)."');", $content);
		
		$tar->addData("xcloner_restore.php", $content);
		
		$tar->close();
		
		if (file_exists($tmp_file)) {
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename="'.basename($tmp_file).'"');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($tmp_file));
		    readfile($tmp_file);
		    
		}
		
		@unlink($tmp_file);
		exit;
	}
	
	/*
	 * 
	 * Download backup by Name from the Storage Path
	 * 
	 */ 
	public function download_backup_by_name()
	{
		$this->check_access();
		
		@ob_end_clean();
		
		$backup_name = $this->xcloner_sanitization->sanitize_input_as_string($_GET['name']);
		
		
		$metadata  = $this->xcloner_file_system->get_storage_filesystem()->getMetadata($backup_name);
		$read_stream  = $this->xcloner_file_system->get_storage_filesystem()->readStream($backup_name);
		
		
		header('Pragma: public');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Cache-Control: private', false);
	    header('Content-Transfer-Encoding: binary');
	    header('Content-Disposition: attachment; filename="'.$metadata['path'].'";');
	    header('Content-Type: application/octet-stream');
	    header('Content-Length: ' . $metadata['size']);
	    
	    @ob_end_clean();
	    
	    $chunkSize = 1024 * 1024;
	    while (!feof($read_stream))
	    {
	        $buffer = fread($read_stream, $chunkSize);
	        echo $buffer;
	    }
	    fclose($read_stream);
	    exit;
    
	}
	
	
	public function restore_upload_backup()
	{
		$this->check_access();
		
		$return['part'] = 0;
		$return['total_parts'] = 0;
		$return['uploaded_size'] = 0;
		$is_multipart = 0;
		
		$file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
		$hash = $this->xcloner_sanitization->sanitize_input_as_string($_POST['hash']);
		
		if(isset($_POST['part']))
			$return['part'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['part']);
		
		if(isset($_POST['uploaded_size']))
			$return['uploaded_size'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['uploaded_size']);
		
		$start = $this->xcloner_sanitization->sanitize_input_as_string($_POST['start']);
		$target_url = $this->xcloner_sanitization->sanitize_input_as_string($_POST['target_url']);
		
		$return['total_size'] = $this->xcloner_file_system->get_backup_size($file);
		
		if($this->xcloner_file_system->is_multipart($file))
		{
			$backup_parts = $this->xcloner_file_system->get_multipart_files($file);
			
			$return['total_parts'] = sizeof($backup_parts)+1;
			
			if($return['part'] and isset($backup_parts[$return['part']-1]))
			{
				$file = $backup_parts[$return['part']-1];
			}
			
			$is_multipart = 1;	
		}
		
		try{
		
			$xcloner_file_transfer = $this->get_xcloner_container()->get_xcloner_file_transfer();
			$xcloner_file_transfer->set_target($target_url);
			$return['start'] = $xcloner_file_transfer->transfer_file($file, $start, $hash);
		
		}catch(Exception $e){
		
			$return = array();
			$return['error'] = true;
			$return['status'] = 500;
			$return['message'] = "CURL communication error with the restore host. ".$e->getMessage();
			$this->send_response( $return, 0);
		
		}
		
		$return['status'] = 200;
		
		//we have finished the upload
		if(!$return['start'] and $is_multipart)
		{
			$return['part']++;
			$return['uploaded_size'] += $this->xcloner_file_system->get_storage_filesystem()->getSize($file);
		}
		
		$this->send_response( $return, 0);
	}
	
	public function restore_backup()
	{
		$this->check_access();
		
		define("XCLONER_PLUGIN_ACCESS", 1);
		include_once(dirname(__DIR__) .DS."restore".DS."xcloner_restore.php");
		return;
	}
	
	/*
	 * 
	 * Send the json response back
	 * 
	 */
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
