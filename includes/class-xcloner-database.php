<?php
/*
 *      class-xcloner-database.php
 *
 *      Copyright 2017 Ovidiu Liuta <info@thinkovi.com>
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */


class XCloner_Database extends wpdb{

	public  $excludedTables 			= array();

	public  $debug 						= 0;
	public  $recordsPerSession			= 10000;
	public  $dbCompatibility			= "";
	public  $dbDropSyntax				= 1;
	public  $countRecords				= 0;

	private  $link;
	private  $db_selected;

	public   $TEMP_DBPROCESS_FILE = "tmp/.database";
	public   $TEMP_DUMP_FILE = "tmp/database-sql.sql";
	
	/*
	 * Initialize the database connection
	 *
	 * name: init
	 * @param array $data {'dbHostname', 'dbUsername', 'dbPassword', 'dbDatabase'}
	 * @return
	 */
	public function init($data, $start = 0){

		if(isset($data['excludedTables']))
			$this->excludedTables 			= $data['excludedTables'];
		if(isset($data['TEMP_DBPROCESS_FILE']))
			$this->TEMP_DBPROCESS_FILE 		= $data['TEMP_DBPROCESS_FILE'];
		if(isset($data['TEMP_DUMP_FILE']))
			$this->TEMP_DUMP_FILE 			= $data['TEMP_DUMP_FILE'];
		if(isset($data['recordsPerSession']))
			$this->recordsPerSession		= $data['recordsPerSession'];
		if(isset($data['dbCompatibility']))
			$this->dbCompatibility			= $data['dbCompatibility'];
		if(isset($data['dbCompatibility']))
			$this->dbDropSyntax				= $data['dbDropSyntax'];

		$this->headers();

		if($start){
				@unlink($this->TEMP_DBPROCESS_FILE);
		}

	}
	

	public function log($message, $error = 0){

		$return = "";
		$date = date("M j, Y @ H:i:s");

		if(($this->debug)){
				//we send the debug message
				//not ready here!!!!!!
				printf("Debug(%s) - %s \n", $date, $message);
		}

		if($error){
				//we have an error message
				throw new Exception(sprintf("Error(%s) - %s \n", $date, $message));
			}

		return;

	}
	
	/*
	 *Return any error
	 *
	 * name: error
	 * @param string $message
	 * @return
	*/
	public function error($message)
	{
		$this->log($message, 1);
		
		return;
	}

	/*
	 * Send some special headers after the connection is initialized
	 *
	 * name: headers
	 * @param
	 * @return
	 */
	private function headers(){
		
		$this->query("SET SQL_QUOTE_SHOW_CREATE=1;");
		$this->query("SET sql_mode = 0;");
		
		$this->set_charset($this->dbh, 'utf8');
		
		if ($this->dbCompatibility)
			$this->set_sql_mode($this->dbCompatibility);

	}

	public function get_database_num_tables($database)
	{
		$query = "show tables in `".$database."`";
		
		$res =  $this->get_results($query);
		return count($res);
	}
	
	public function get_all_databases()
	{
		$query = "SHOW DATABASES;";
		
		$databases = $this->get_results($query);
		
		$databases_list = array();
		
		$i = 0;
		
		$databases_list[$i]['name'] = $this->dbname;
		$databases_list[$i]['num_tables'] = $this->get_database_num_tables($this->dbname);
		$i++;
		
		foreach( $databases as $db){
			if($db->Database != $this->dbname)
			{
				$databases_list[$i]['name'] = $db->Database;
				$databases_list[$i]['num_tables'] = $this->get_database_num_tables($db->Database);
				$i++;
			}
		}
		
		return $databases_list;
	}
	
	/*
	 * Returns an array of tables from a database and mark $2excluded ones
	 *
	 * name: lisTables
	 * @param array $excluded array of tables to mark as excluded
	 * @return array $tablesList
	 */
	public function listTables($database = "", $excluded, $get_num_records = 0){

		$tablesList[0] = array( );
		$inc = 0;

		if(!$database)
			$database = $this->dbname;
				
		$tables = $this->get_results("SHOW TABLES in `".$database."`");

		foreach ($tables as $table){
			$tablesList[$inc]['name'] = $table->Tables_in_wordpress;
			$tablesList[$inc]['database'] = $database;

			if($get_num_records)
			{
				$records_num_result = $this->get_var("SELECT count(*) FROM `".$database."`.`".$table->Tables_in_wordpress."`");
				$tablesList[$inc]['records'] = $records_num_result;
			}

			if(is_array($excluded))
				if( in_array($row[0], $excluded) )
					$tablesList[$inc]['excluded'] = 1;
			$inc++;
        }

		return $tablesList;

	}

	public function writeTempFile(){

		$tables = $this->listTables($this->excludedTables);

		$fp = fopen($this->TEMP_DBPROCESS_FILE, "a");

		if($fp){

			fwrite($fp, sprintf("###newdump###\t%s\t%s\n", $this->dbname, $this->TEMP_DUMP_FILE));

			// write this to the class and write to $TEMP_DBPROCESS_FILE file as database.table records
			foreach($tables as $key=>$table) if($table!= ""){

				$tables[$key]['records'] = 0;

				if(!$tables[$key]['excluded'])
					$tables[$key]['records'] = $this->countRecords($tables[$key]['table']);

				$tmp = sprintf("`%s`.`%s`\t%s\t%s\n", $this->dbname, $tables[$key]['table'], $tables[$key]['records'], $tables[$key]['excluded']);
				fwrite($fp, $tmp);
			}

			fwrite($fp, "###enddump###\n");
			fclose($fp);
		}
		else{
			$this->error("Unable to open for writing file ".$this->TEMP_DBPROCESS_FILE);
		}

	}

	/*
	 * Returns the number of records from a table
	 *
	 * name: countRecords
	 * @param string $table - the source table
	 * @return int $count
	 */
	public function countRecords($table){

			$table = "`".$this->dbname."`.`$table`";

			$result = $this->get_var("SELECT count(*) FROM $table;");

			return intval($result) ;// not max limit on 32 bit systems 2147483647; on 64 bit 9223372036854775807

	}

	/*
	 *	Processing the mysql backup incrementally
	 *
	 * name: processIncremental
	 * @param
	 * 		int $startAtLine - at which line from the perm.txt file to start reading
	 * 		int startAtRecord - at which record to start from the table found at $startAtLine
	 * 		string $dumpfie	- where to save the data
	 * 		string $dbCompatibility - MYSQL40, MYSQ32, none=default
	 * 		int $dbDropSyntax	- check if the DROP TABLE syntax should be added
	 * @return array $return
	 */
	public function processIncremental($startAtLine= 0, $startAtRecord = 0, $dumpfile = "", $dbCompatibility= "", $dbDropSyntax= ""){

		$count = 0;
		$return = array();

		$this->log("Starting new process at line $startAtLine from record $startAtRecord", 1);

		$fp = fopen($this->TEMP_DBPROCESS_FILE, "r");
		if($fp){

			while (($buffer = fgets($fp, 4096)) !== false){

				if($count == $startAtLine){

					$buffer = str_replace("\n", "", $buffer);
					$tableInfo =explode("\t", $buffer);
					//print_r($tableInfo);
					if($tableInfo[0] == "###newdump###"){
							// we create a new mysql dump file
							if($dumpfile != ""){
									// we finished a previous one and write the footers
									$return['dumpsize'] = $this->dataFooters($dumpfile);
							}

							$dump = fopen($tableInfo[2], "w");
							fwrite($dump, $this->dataHeaders($tableInfo[1]));
							$startAtLine++;
							fclose($dump);
							$dumpfile = $tableInfo[2];

							$return['newDump'] = 1;
							//break;
						}
						else{
							//we export the table
							if($tableInfo[0] == "###enddump###")
								$return['endDump'] = 1;

							$fd = fopen($dumpfile, "a");

							if($fd){

								$next = $startAtRecord + $this->recordsPerSession;
								// $tableInfo[1] number of records in the table
								$table = explode("`.`", $tableInfo[0]);
								$tableName = str_replace("`", "", $table[1]);
								$databaseName = str_replace("`", "", $table[0]);

								//return something to the browser
								$return['tableName'] 		= $tableName;
								$return['databaseName'] 	= $databaseName;
								$return['totalRecords'] 	= $tableInfo[1];

								//if(intval($return['totalRecords']) != 0)
								if(trim($tableName) !=""  and !$tableInfo[2])
									$this->exportTable($databaseName, $tableName, $startAtRecord, $this->recordsPerSession, $fd);

								fclose($fd);

								if($next > $tableInfo[1]) //we finished loading the records for next sessions, will go to the new record
								{
										$startAtLine ++;
										$startAtRecord = 0;
								}else{
										$startAtRecord = $startAtRecord + $this->recordsPerSession;
									}

								//$return['dbCompatibility'] 	= self::$dbCompatibility;
								//$return['dbDropSyntax']		= self::$dbDropSyntax;
								$return['startAtLine']		= $startAtLine;
								$return['startAtRecord']	= $startAtRecord;
								$return['dumpfile']			= $dumpfile;

								return $return;
								break;

							}else{
								$this->error("Unable to open for writing file $dumpfile");
							}
						}

				}

				$count++;


			}

			//while is finished, lets go home...
			if($dumpfile != ""){
				// we finished a previous one and write the footers
				$return['dumpsize'] = $this->dataFooters($dumpfile);
			}
			$return['finished'] = 1;
			$return['startAtLine']	= $startAtLine;

			return $return;

		}else{
			$this->error("Unable to open for reading file ".$this->TEMP_DBPROCESS_FILE);
		}


	}


	/*
	 * Exporting the table records
	 *
	 * name: exportTable
	 * @param
	 * 		string $databaseName - database name of the table
	 * 		string tableName - table name
	 * 		int $start - where to start from
	 * 		int $limit - how many records
	 * 		handler $fd - file handler where to write the records
	 * @return
	 */
	public function exportTable($databaseName, $tableName, $start, $limit, $fd){

		if($start == 0)
			$this->dumpStructure($databaseName, $tableName, $fd);

		$start = intval($start);
		$limit = intval($limit);
		//exporting the table content now

		$result = mysqli_query($this->dbh, "SELECT * from `$databaseName`.`$tableName` Limit $start, $limit ;");
		if($result){
			while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){

					fwrite($fd, "INSERT INTO `$tableName` VALUES (");
					$arr = $row;
					$buffer = "";
					$this->countRecords++;

	                foreach ($arr as $key => $value) {
						$value = mysqli_real_escape_string($this->dbh, $value);
						$buffer .= "'".$value."', ";
					}
					$buffer = rtrim($buffer, ', ') . ");\n";
					fwrite($fd, $buffer);
					unset($buffer);

				}
		}

	}

	public function dumpStructure($databaseName, $tableName ,$fd){

		fwrite($fd, "\n#\n# Table structure for table `$tableName`\n#\n\n");

        if ($this->dbDropSyntax)
			fwrite($fd, "\nDROP table IF EXISTS `$tableName`;\n");

		$result = mysqli_query($this->dbh,"SHOW CREATE table `$databaseName`.`$tableName`;");
		if($result){
			$row = mysqli_fetch_row( $result);
			fwrite($fd, $row[1].";\n");
		}

		fwrite($fd, "\n#\n# End Structure for table `$tableName`\n#\n\n");
		fwrite($fd, "#\n# Dumping data for table `$tableName`\n#\n\n");
		return;

	}

	public function dataFooters($dumpfile){

		// we finished the dump file, not return the size of it
		$ftemp = fopen($dumpfile, "a");
		if($ftemp){
			fwrite($ftemp, "\n#\n# Finished at: ".date("M j, Y \a\\t H:i")."\n#");
			fclose($ftemp);
		}else{
			$this->error("Unable to open file $ftemp for writing");
			}

		return sprintf("%u", filesize($dumpfile));

	}

	public function resetcountRecords(){
		$this->$countRecords = 0;

		return $this->countRecords;
	}

	public function getcountRecords(){
		return $this->countRecords;
	}


	public function dataHeaders($database){

		$return = "";

		$return .= "#\n";
		$return .= "# Powered by XCloner Site Backup\n";
		$return .= "# http://www.xcloner.com\n";
		$return .= "#\n";
		$return .= "# Host: " . $_SERVER['HTTP_HOST'] . "\n";
		$return .= "# Generation Time: " . date("M j, Y \a\\t H:i") . "\n";
		$return .= "# PHP Version: " . phpversion() . "\n";
		$return .= "# Mysql Compatibility: ". $this->dbCompatibility . "\n";

		$result = mysqli_query($this->dbh, "SHOW VARIABLES LIKE \"%version%\";");
		if($result){
			while($row = mysqli_fetch_array($result)){

					$return .= "# MYSQL ".$row[0].": ".$row[1]."\n";

				}
		}

		$return .= "#\n# Database : `" . $database . "`\n# --------------------------------------------------------\n\n";
		return $return;

	}


}
