<?php

class Xcloner_File_Transfer extends Xcloner_File_System{
	
	private $target_url;
	private $transfer_limit = 1048576; //bytes 1MB= 1048576 300KB = 358400
	
	
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
		
		$tmp_filename = "xcloner_upload_".substr(md5(time()), 0, 5);
		
		$this->get_tmp_filesystem()->write($tmp_filename, $binary_data);
		
		$tmp_file_path = $this->get_tmp_filesystem_adapter()->applyPathPrefix($tmp_filename);

		$send_array = array();
		
		$send_array['file'] 	= $file;
		$send_array['start'] 	= $start;
		$send_array['xcloner_action'] 	= "write_file";
		$send_array['hash'] 	= $hash;
		#$send_array['blob'] 	= $binary_data;
		$send_array['blob'] 	= $this->curl_file_create($tmp_file_path,'application/x-binary',$tmp_filename);
		
		//$data = http_build_query($send_array);
		
		$this->get_logger()->info(sprintf("Sending curl request to %s with %s data of file %s starting position %s using temporary file %s", $this->target_url, $this->transfer_limit, $file, $start, $tmp_filename));
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->target_url);
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1200);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
		curl_setopt($ch, CURLOPT_POSTFIELDS, $send_array );
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		
		$original_result = curl_exec ($ch);
		
		$this->get_tmp_filesystem()->delete($tmp_filename);
		
		$result = json_decode($original_result);
				
		if(!$result)
			throw new Exception("We have received no valid response from the remote host, original message: ". $original_result);
			
		if($result->status != 200)
		{
			throw new Exception($result->response);
		}
		
		if(ftell($fp) >= $this->get_storage_filesystem()->getSize($file))
		{
			$this->get_logger()->info(sprintf("Upload done for file %s to target url %s, transferred a total of %s bytes", $file, $this->target_url, ftell($fp)));
			$this->remove_tmp_filesystem();
			return false;
		}
		
		return ftell($fp);
	}
	
	private function curl_file_create($filename, $mimetype = '', $postname = '') {
		if (!function_exists('curl_file_create')) {
	        
	        return "@$filename;filename="
	            . ($postname ?: basename($filename))
	            . ($mimetype ? ";type=$mimetype" : '');
		
		}else{
			
			return curl_file_create($filename, $mimetype, $postname);	
				
		}
	}
}
