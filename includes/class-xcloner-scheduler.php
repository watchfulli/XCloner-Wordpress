<?php

class Xcloner_Scheduler{
	
	private $db;
	private $scheduler_table = "xcloner_scheduler";
	
	private $xcloner_remote_storage;
	private $archive_system;
	private $xcloner_database;
	private $xcloner_settings;
	private $logger;
	private $xcloner_file_system;
	
	private $allowed_schedules = array("hourly", "twicedaily", "daily", "weekly", "monthly");
	/*public function __call($method, $args) {
		echo "$method is not defined";
	}*/

	public function __construct(Xcloner $xcloner_container)
	{
		global $wpdb;
		
		$this->db 					= $wpdb;
		$wpdb->show_errors			= false;
		
		$this->xcloner_container	= $xcloner_container;
		$this->xcloner_settings 	= $xcloner_container->get_xcloner_settings();
		
		$this->scheduler_table 		= $this->db->prefix.$this->scheduler_table;
	}
	
	private function get_xcloner_container()
	{
		return $this->xcloner_container;
	}
	
	private function set_xcloner_container(Xcloner $container)
	{
		$this->xcloner_container = $container;
	}
	
	public function get_scheduler_list($return_only_enabled = 0 )
	{
		$list = $this->db->get_results("SELECT * FROM ".$this->scheduler_table);
		
		if($return_only_enabled)
		{
			$new_list= array();
			
			foreach($list as $res)
				if($res->status)
				{
					$res->next_run_time = wp_next_scheduled('xcloner_scheduler_'.$res->id, array($res->id))+(get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
					$new_list[] = $res;
				}
			$list = $new_list;	
		}
		return $list;
	}
	
	public function get_next_run_schedule($xcloner_file_system = "")
	{
		$list = $this->get_scheduler_list($return_only_enabled = 1);

		return $list;
	}
	
	public function get_schedule_by_id_object($id)
	{
		$data = $this->db->get_row("SELECT * FROM ".$this->scheduler_table." WHERE id=".$id);
		
		return $data;
	}
	
	public function get_schedule_by_id($id)
	{
		$data = $this->db->get_row("SELECT * FROM ".$this->scheduler_table." WHERE id=".$id, ARRAY_A);
		
		if(!$data)
			return false;
		
		$params = json_decode($data['params']);
		
		//print_r($params);
		$data['params'] = "";
		$data['backup_params'] = $params->backup_params;
		$data['table_params'] = json_encode($params->database);
		$data['excluded_files'] = json_encode($params->excluded_files);
		
		
		return $data;
	}
	
	public function delete_schedule_by_id($id)
	{
		$hook =  'xcloner_scheduler_'.$id;
		wp_clear_scheduled_hook( $hook, array($id) );
		
		$data = $this->db->delete( $this->scheduler_table , array( 'id' => $id ) );
		
		return $data;
	}
	
	public function deactivate_wp_cron_hooks()
	{
		$list = $this->get_scheduler_list();
		
		foreach($list as $schedule)
		{
			$hook =  'xcloner_scheduler_'.$schedule->id;
			
			$timestamp = wp_next_scheduled( $hook , array($schedule->id) );
			wp_unschedule_event( $timestamp, $hook, array($schedule->id) );
		}
	}
	
	public function update_wp_cron_hooks()
	{
		$list = $this->get_scheduler_list();
		
		foreach($list as $schedule)
		{
			$hook =  'xcloner_scheduler_'.$schedule->id;
			
			//adding the xcloner_scheduler hook with xcloner_scheduler_callback callback
			add_action( $hook, array($this, 'xcloner_scheduler_callback'), 10,  1 );
			
			if ( ! wp_next_scheduled( $hook, array($schedule->id) ) and $schedule->status) {
				
				if($schedule->recurrence == "single")
				{
					wp_schedule_single_event( strtotime($schedule->start_at), $hook, array($schedule->id));
				}else{	
					wp_schedule_event( strtotime($schedule->start_at), $schedule->recurrence, $hook, array($schedule->id) );
				}
					
			}elseif(!$schedule->status)
			{
				$timestamp = wp_next_scheduled( $hook , array($schedule->id) );
				wp_unschedule_event( $timestamp, $hook, array($schedule->id) );
			}
		}
	
	}
	
	public function update_cron_hook($id)
	{
		$schedule = $this->get_schedule_by_id_object($id);
		$hook =  'xcloner_scheduler_'.$schedule->id;
		
		$timestamp = wp_next_scheduled( $hook , array($schedule->id) );
		wp_unschedule_event( $timestamp, $hook, array($schedule->id) );
		
		if ($schedule->status) {
			
			if($schedule->recurrence == "single")
			{
				wp_schedule_single_event( strtotime($schedule->start_at), $hook, array($schedule->id));
			}else{	
				wp_schedule_event( strtotime($schedule->start_at), $schedule->recurrence, $hook, array($schedule->id) );
			}
				
		}
}
	
	public function disable_single_cron($schedule_id)
	{
		$hook =  'xcloner_scheduler_'.$schedule_id;
		$timestamp = wp_next_scheduled( $hook , array($schedule_id) );
		wp_unschedule_event( $timestamp, $hook, array($schedule_id) );
		
		$schedule['status'] = 0;
		
		$update = $this->db->update( 
				$this->scheduler_table, 
				$schedule, 
				array( 'id' => $schedule_id ), 
				array( 
					'%s', 
					'%s' 
				) 
				);
		return $update;		
	}
	
	public function update_hash($schedule_id, $hash)
	{
		$schedule['hash'] = $hash;
		
		$update = $this->db->update( 
				$this->scheduler_table, 
				$schedule, 
				array( 'id' => $schedule_id ), 
				array( 
					'%s', 
					'%s' 
				) 
				);
		return $update;		
	} 
	
	public function update_last_backup($schedule_id, $last_backup)
	{
		$schedule['last_backup'] = $last_backup;
		
		$update = $this->db->update( 
				$this->scheduler_table, 
				$schedule, 
				array( 'id' => $schedule_id ), 
				array( 
					'%s', 
					'%s' 
				) 
				);
		return $update;		
	} 
	
	private function __xcloner_scheduler_callback($id, $schedule)
	{
		set_time_limit(0);
		
		$xcloner = new XCloner();
		$xcloner->init();
		$this->set_xcloner_container($xcloner);
		
		#$hash = $this->xcloner_settings->get_hash();
		#$this->get_xcloner_container()->get_xcloner_settings()->set_hash($hash);
		
		//$this->xcloner_settings 		= $this->get_xcloner_container()->get_xcloner_settings();		
		$this->xcloner_file_system 		= $this->get_xcloner_container()->get_xcloner_filesystem();
		$this->xcloner_database 		= $this->get_xcloner_container()->get_xcloner_database();
		$this->archive_system 			= $this->get_xcloner_container()->get_archive_system();
		$this->logger 					= $this->get_xcloner_container()->get_xcloner_logger()->withName("xcloner_scheduler");
		$this->xcloner_remote_storage 	= $this->get_xcloner_container()->get_xcloner_remote_storage();
		
		$this->logger->info(sprintf("New schedule hash is %s", $this->xcloner_settings->get_hash()));
		
		if(isset($schedule['backup_params']->diff_start_date) && $schedule['backup_params']->diff_start_date)
		{
			$this->xcloner_file_system->set_diff_timestamp_start($schedule['backup_params']->diff_start_date);
		}
				
		if($schedule['recurrence'] == "single")
		{
			$this->disable_single_cron($schedule['id']);
		}
		
		if(!$schedule)
		{
			$this->logger->info(sprintf("Could not load schedule with id'%s'", $id), array("CRON"));
			return;
		}
		
		//echo $this->get_xcloner_container()->get_xcloner_settings()->get_hash(); exit;
		
		$this->update_hash($schedule['id'], $this->xcloner_settings->get_hash());
		
		$this->logger->info(sprintf("Starting cron schedule '%s'", $schedule['name']), array("CRON"));
		
		$this->xcloner_file_system->set_excluded_files(json_decode($schedule['excluded_files']));
		
		$init = 1;
		$continue = 1;

		while($continue)
		{
			$continue = $this->xcloner_file_system->start_file_recursion($init);
			
			$init = 0;
		}
		
		$this->logger->info(sprintf("File scan finished"), array("CRON"));
		
		$this->logger->info(sprintf("Starting the database backup"), array("CRON"));
		
		$init = 1;
		$return['finished'] = 0;
		
		while(!$return['finished'])
		{
			$return  = $this->xcloner_database->start_database_recursion((array)json_decode($schedule['table_params']), $return, $init);
			$init = 0;
		}
		
		$this->logger->info(sprintf("Database backup done"), array("CRON"));
		
		$this->logger->info(sprintf("Starting file archive process"), array("CRON"));
		
		$init = 0;
		$return['finished'] = 0;
		$return['extra'] = array();
		
		while(!$return['finished'])
		{
			$return = $this->archive_system->start_incremental_backup((array)$schedule['backup_params'], $return['extra'], $init);
			$init = 0;
		}
		$this->logger->info(sprintf("File archive process FINISHED."), array("CRON"));
		
		//getting the last backup archive file
		$return['extra']['backup_parent'] = $this->archive_system->get_archive_name_with_extension();
		if($this->xcloner_file_system->is_part($this->archive_system->get_archive_name_with_extension()))
				$return['extra']['backup_parent'] = $this->archive_system->get_archive_name_multipart();
				
		$this->update_last_backup($schedule['id'], $return['extra']['backup_parent']);
		
		if(isset($schedule['remote_storage']) && $schedule['remote_storage'] && array_key_exists($schedule['remote_storage'], $this->xcloner_remote_storage->get_available_storages()))
		{
			$backup_file = $return['extra']['backup_parent'];
			
			$this->logger->info(sprintf("Transferring backup to remote storage %s", strtoupper($schedule['remote_storage'])), array("CRON"));
			
			if(method_exists($this->xcloner_remote_storage, "upload_backup_to_storage"))
				call_user_func_array(array($this->xcloner_remote_storage, "upload_backup_to_storage"), array($backup_file, $schedule['remote_storage']));
		}
		
		if(isset($schedule['backup_params']->email_notification) and $to=$schedule['backup_params']->email_notification)
		{	
			try{
				$from = "";
				$additional['lines_total'] = $return['extra']['lines_total'];
				$subject = sprintf(__("%s - new backup generated %s") , $schedule['name'], $return['extra']['backup_parent']);
				
				$this->archive_system->send_notification($to, $from, $subject, $return['extra']['backup_parent'], $schedule, "", $additional);
				
				//CHECK IF WE SHOULD DELETE BACKUP AFTER REMOTE TRANSFER IS DONE
				if($schedule['remote_storage'] && $this->xcloner_settings->get_xcloner_option('xcloner_cleanup_delete_after_remote_transfer'))
				{
					$this->logger->info(sprintf("Deleting %s from local storage matching rule xcloner_cleanup_delete_after_remote_transfer",$return['extra']['backup_parent']));
					$this->xcloner_file_system->delete_backup_by_name($return['extra']['backup_parent']);
					
				}
			}catch(Exception $e)
			{
				$this->logger->error($e->getMessage());
			}
		}
		
		$this->xcloner_file_system->remove_tmp_filesystem();
		
		$this->xcloner_file_system->backup_storage_cleanup();
	}
	
	public function xcloner_scheduler_callback($id, $schedule = "")
	{
		if($id)
		{
			$schedule = $this->get_schedule_by_id($id);
		}
		
		try{

			$this->__xcloner_scheduler_callback($id, $schedule);
			
		}catch(Exception $e){
			
			//send email to site admin if email notification is not set in the scheduler
			if(!isset($schedule['backup_params']->email_notification) || !$schedule['backup_params']->email_notification)
			{
				$schedule['backup_params']->email_notification = get_option('admin_email');
			}
				
			if(isset($schedule['backup_params']->email_notification) && $to=$schedule['backup_params']->email_notification)
			{
				$from = "";
				$this->archive_system->send_notification($to, $from, $schedule['name']." - backup error","", "", $e->getMessage());
			}
			
		}
		
	}
	
	public function get_available_intervals()
	{
		$schedules = wp_get_schedules();
		$new_schedules = array();
		
		foreach ($schedules as $key => $row) {
			if(in_array($key, $this->allowed_schedules))
			{
				$new_schedules[$key] = $row;
				$intervals[$key]  = $row['interval'];
			}
		}
		
		array_multisort($intervals, SORT_ASC, $new_schedules);
		return $new_schedules;
	}
	
	
}
