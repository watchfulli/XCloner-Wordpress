<?php

class Xcloner_File_System{
	
	function list_directory($path)
	{
		$files = scandir($path);
		$return = array();
		$i = 0;
		
		foreach($files as $file)
		{
			$filesize = 0;
			
			if($file == "." or $file == "..")
				continue;
				
			if(is_dir($path.DS.$file))
			{
				$type = 'D';
			}elseif(is_file($path.DS.$file))
			{
				$type = 'F';
				$filesize = filesize($path.DS.$file);
			}elseif(is_link($path.DS.$file))
			{
				$type = 'L';
			}
			
			$return[$type.$i]['filename'] = $file;
			$return[$type.$i]['type'] = $type;
			$return[$type.$i]['size'] = $filesize;
			$i++;
		}
		
		ksort($return);
		
		return $return;
	}
}
