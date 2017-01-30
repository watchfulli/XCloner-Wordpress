<?php

class Xcloner_Scheduler{
	
	private $db;
	private $scheduler_table = "xcloner_scheduler";
	
	public function __construct()
	{
		global $wpdb;
		$this->db = $wpdb;
		
		$this->scheduler_table = $this->db->prefix.$this->scheduler_table;
	}

	public function get_scheduler_list()
	{
		$list = $this->db->get_results("SELECT * FROM ".$this->scheduler_table);
		
		return $list;
	}
	
	public function get_schedule_by_id($id)
	{
		$data = $this->db->get_row("SELECT * FROM ".$this->scheduler_table." WHERE id=".$id, ARRAY_A);
		
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
}
