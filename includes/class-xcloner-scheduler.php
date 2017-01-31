<?php

class Xcloner_Scheduler{
	
	private $db;
	private $scheduler_table = "xcloner_scheduler";
	
	
	/*public function __call($method, $args) {
		echo "$method is not defined";
	}*/

	public function __construct()
	{
		global $wpdb;
		$this->db = $wpdb;
		
		$wpdb->show_errors				= false;
		
		$this->xcloner_settings 		= new Xcloner_Settings();
		
		$this->scheduler_table 			= $this->db->prefix.$this->scheduler_table;
	}

	public function get_scheduler_list()
	{
		$list = $this->db->get_results("SELECT * FROM ".$this->scheduler_table);
		
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
				
				//echo $schedule->start_at; exit;
				if($schedule->recurrence == "single")
					wp_schedule_single_event( strtotime($schedule->start_at), $hook, array($schedule->id));
				else	
					wp_schedule_event( strtotime($schedule->start_at), $schedule->recurrence, $hook, array($schedule->id) );
					
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
				wp_schedule_single_event( strtotime($schedule->start_at), $hook, array($schedule->id));
			else{	
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
		
		$insert = $this->db->update( 
				$this->scheduler_table, 
				$schedule, 
				array( 'id' => $schedule_id ), 
				array( 
					'%s', 
					'%s' 
				) 
				);
	}
	
	public function xcloner_scheduler_callback($id)
	{
		set_time_limit(0);
		
		$this->xcloner_settings->generate_new_hash();
		
		$this->xcloner_file_system 		= new Xcloner_File_System($this->xcloner_settings->get_hash());
		$this->xcloner_database 		= new XCloner_Database($this->xcloner_settings->get_hash());
		$this->archive_system 			= new Xcloner_Archive($this->xcloner_settings->get_hash());
		$this->logger 					= new XCloner_Logger('xcloner_file_system');
		
		$schedule = $this->get_schedule_by_id($id);
		
		
		if($schedule['recurrence'] == "single")
		{
			$this->disable_single_cron($schedule['id']);
		}
		
		if(!$schedule)
		{
			$this->logger->info(sprintf("Could not load schedule with id'%s'", $id), array("CRON"));
			return;
		}
		
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
		$extra = array();
		$return['finished'] = 0;
		
		while(!$return['finished'])
		{
			$return  = $this->xcloner_database->start_database_recursion((array)json_decode($schedule['table_params']), $return, $init);
			$init = 0;
		}
		
		$this->logger->info(sprintf("Database backup done"), array("CRON"));
		
		$this->logger->info(sprintf("Starting file archive process"), array("CRON"));
		
		$init = 0;
		$extra = array();
		$return['finished'] = 0;
		$return['extra'] = array();
		
		while(!$return['finished'])
		{
			$return = $this->archive_system->start_incremental_backup((array)$schedule['backup_params'], $return['extra'], $init);
			$init = 0;
		}
		$this->logger->info(sprintf("File archive process FINISHED."), array("CRON"));
		
	}
	
	
}
