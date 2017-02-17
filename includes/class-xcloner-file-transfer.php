<?php

class Xcloner_File_Transfer extends Xcloner_File_System{
	
	private $target_url;
	private $transfer_limit = 358400; //bytes
	
	public function set_target($target_url)
	{
		return $this->target_url= $target_url;
	}
	
	public function get_target()
	{
		return $this->target_url;
	}
	
	
	public function transfer_file($file, $start = 0, $hash = "")
	{
		if(!$this->target_url)
			throw new Exception("Please setup a target url for upload");

		
		$fp = $this->get_storage_filesystem()->readStream($file);
		
		fseek($fp, $start, SEEK_SET);
		
		$binary_data =  fread($fp, $this->transfer_limit);
		
		$send_array = array();
		$send_array['blob'] 	= $binary_data;
		$send_array['file'] 	= $file;
		$send_array['start'] 	= $start;
		$send_array['action'] 	= "write_file";
		$send_array['hash'] 	= $hash;
		
		$data = http_build_query($send_array);
		
		$this->get_logger()->info(sprintf("Sending curl request to %s with %s data of file %s starting position %s", $this->target_url, $this->transfer_limit, $file, $start));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->target_url);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1200);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $data );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		
		$result = curl_exec ($ch);
		
		$result = json_decode($result);
				
		if(!$result)
			throw new Exception("We have received no response from the remote host");
			
		if($result->status != 200)
		{
			throw new Exception($result->response);
			return false;
		}
		
		if(ftell($fp) >= $this->get_storage_filesystem()->getSize($file))
		{
			$this->get_logger()->info(sprintf("Upload done for file %s to target url %s, transferred a total of %s bytes", $file, $this->target_url, ftell($fp)));
			return false;
		}
		
		return ftell($fp);
	}
}
