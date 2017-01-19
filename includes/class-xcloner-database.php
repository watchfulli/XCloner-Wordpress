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


class XCloner_Database{

	public static $dbHostname 			= "localhost";
	public static $dbUsername 			= "root";
	public static $dbPassword 			= "";
	public static $dbDatabase 			= "";
	public static $excludedTables 		= array();

	public static $debug 				= 0;
	public static $recordsPerSession	= 10000;
	public static $dbCompatibility		= "";
	public static $dbDropSyntax			= 1;
	public static $countRecords			= 0;

	private static $link;
	private static $db_selected;

	public static  $TEMP_DBPROCESS_FILE = "tmp/.database";
	public static  $TEMP_DUMP_FILE = "tmp/database-sql.sql";

	/*
	 * Initialize the database connection
	 *
	 * name: init
	 * @param array $data {'dbHostname', 'dbUsername', 'dbPassword', 'dbDatabase'}
	 * @return
	 */
	public function init($data, $start = 0){

		self::$dbHostname 				= $data['dbHostname'];
		self::$dbUsername 				= $data['dbUsername'];
		self::$dbPassword 				= $data['dbPassword'];
		self::$dbDatabase 				= $data['dbDatabase'];
		
		if(isset($data['excludedTables']))
			self::$excludedTables 			= $data['excludedTables'];
		if(isset($data['TEMP_DBPROCESS_FILE']))
			self::$TEMP_DBPROCESS_FILE 		= $data['TEMP_DBPROCESS_FILE'];
		if(isset($data['TEMP_DUMP_FILE']))
			self::$TEMP_DUMP_FILE 			= $data['TEMP_DUMP_FILE'];
		if(isset($data['recordsPerSession']))
			self::$recordsPerSession		= $data['recordsPerSession'];
		if(isset($data['dbCompatibility']))
			self::$dbCompatibility			= $data['dbCompatibility'];
		if(isset($data['dbCompatibility']))
			self::$dbDropSyntax				= $data['dbDropSyntax'];

		self::connect();
		self::headers();

		if($start){
				@unlink(self::$TEMP_DBPROCESS_FILE);
		}

	}
	
	/*
	 *Return any error
	 *
	 * name: error
	 * @param string $message
	 * @return
	*/
	public function error($message, $force = ""){

		$return = "";
		$date = date("M j, Y @ H:i:s");

		if((self::$debug) && ($force)){
				//we have debug message as force is 1
				printf("Debug(%s) - %s \n", $date, $message);
		}

		if(!$force){
				//we have an error message
				printf("Error(%s) - %s \n", $date, $message);
			}

		return;

	}

	/*
	 * Connect to the database
	 *
	 * name: connect
	 * @param
	 * @return
	 */
	public function connect(){

		self::$link = new mysqli(self::$dbHostname, self::$dbUsername, self::$dbPassword, self::$dbDatabase);
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}

	}

	/*
	 * Disconnect from the database
	 *
	 * name: disconnect
	 * @param
	 * @return
	 */
	public function disconnect(){

		//mysqli_close(self::$link);

	}

	/*
	 * Send some special headers after the connection is initialized
	 *
	 * name: headers
	 * @param
	 * @return
	 */
	private function headers(){

		self::query("SET SQL_QUOTE_SHOW_CREATE=1;");
		self::query("SET sql_mode = 0;");
		mysqli_set_charset(self::$link, 'utf8');
		if (self::$dbCompatibility)
			self::query("SET sql_mode=" . self::$dbCompatibility . ";");

	}

	/*
	 * Run a mysql qeury
	 *
	 * name: query
	 * @param string $query Query to run
	 * @return $result or false
	 */
	public function query($query){

		$result = mysqli_query(self::$link, $query.";");
		self::error($query, 1);

		if (!$result) {
			self::error('Invalid query: ' . mysqli_error(self::$link));
			return false;
		}
		else{
			return $result;
		}

	}

	
	public function get_all_databases()
	{
		$query = "SHOW DATABASES;";
		
		$result = $this->query($query);
		
		$databases_list = array();
		
		$databases_list[]['name'] = self::$dbDatabase;
		
		while ($row = mysqli_fetch_array( $result)){
			if($row[0] != self::$dbDatabase)
				$databases_list[]['name'] = $row[0];
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
			$database = self::$dbDatabase;
				
		$result = self::query("SHOW TABLES in `".$database."`");

		while ($row = mysqli_fetch_array( $result)){
			$tablesList[$inc]['name'] = $row[0];
			$tablesList[$inc]['database'] = $database;

			if($get_num_records)
			{
				$records_num_result = self::query("SELECT count(*) FROM `".$database."`.`".$row[0]."`");
				$row = mysqli_fetch_row($records_num_result);
				$tablesList[$inc]['records'] = $row[0];
			}

			if(is_array($excluded))
				if( in_array($row[0], $excluded) )
					$tablesList[$inc]['excluded'] = 1;
			$inc++;
        }

		return $tablesList;

	}

	public function writeTempFile(){

		$tables = self::listTables(self::$excludedTables);

		$fp = fopen(self::$TEMP_DBPROCESS_FILE, "a");

		if($fp){

			fwrite($fp, sprintf("###newdump###\t%s\t%s\n", self::$dbDatabase, self::$TEMP_DUMP_FILE));

			// write this to the class and write to $TEMP_DBPROCESS_FILE file as database.table records
			foreach($tables as $key=>$table) if($table!= ""){

				$tables[$key]['records'] = 0;

				if(!$tables[$key]['excluded'])
					$tables[$key]['records'] = self::countRecords($tables[$key]['table']);

				$tmp = sprintf("`%s`.`%s`\t%s\t%s\n", self::$dbDatabase, $tables[$key]['table'], $tables[$key]['records'], $tables[$key]['excluded']);
				fwrite($fp, $tmp);
			}

			fwrite($fp, "###enddump###\n");
			fclose($fp);
		}
		else{
			self::error("Unable to open for writing file ".self::$TEMP_DBPROCESS_FILE);
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

			$table = "`".self::$dbDatabase."`.`$table`";

			$result = self::query("SELECT count(*) FROM $table;");

			$count = mysqli_fetch_row($result);

			return intval($count[0]) ;// not max limit on 32 bit systems 2147483647; on 64 bit 9223372036854775807

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

		self::error("Starting new process at line $startAtLine from record $startAtRecord", 1);

		$fp = fopen(self::$TEMP_DBPROCESS_FILE, "r");
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
									$return['dumpsize'] = self::dataFooters($dumpfile);
							}

							$dump = fopen($tableInfo[2], "w");
							fwrite($dump, self::dataHeaders($tableInfo[1]));
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

								$next = $startAtRecord + self::$recordsPerSession;
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
									self::exportTable($databaseName, $tableName, $startAtRecord, self::$recordsPerSession, $fd);

								fclose($fd);

								if($next > $tableInfo[1]) //we finished loading the records for next sessions, will go to the new record
								{
										$startAtLine ++;
										$startAtRecord = 0;
								}else{
										$startAtRecord = $startAtRecord + self::$recordsPerSession;
									}

								//$return['dbCompatibility'] 	= self::$dbCompatibility;
								//$return['dbDropSyntax']		= self::$dbDropSyntax;
								$return['startAtLine']		= $startAtLine;
								$return['startAtRecord']	= $startAtRecord;
								$return['dumpfile']			= $dumpfile;

								return $return;
								break;

							}else{
								self::error("Unable to open for writing file $dumpfile");
							}
						}

				}

				$count++;


			}

			//while is finished, lets go home...
			if($dumpfile != ""){
				// we finished a previous one and write the footers
				$return['dumpsize'] = self::dataFooters($dumpfile);
			}
			$return['finished'] = 1;
			$return['startAtLine']	= $startAtLine;

			return $return;

		}else{
			self::error("Unable to open for reading file ".self::$TEMP_DBPROCESS_FILE);
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
			self::dumpStructure($databaseName, $tableName, $fd);

		$start = intval($start);
		$limit = intval($limit);
		//exporting the table content now

		$result = self::query("SELECT * from `$databaseName`.`$tableName` Limit $start, $limit ;");
		if($result){
			while($row = mysqli_fetch_array($result, MYSQL_ASSOC)){

					fwrite($fd, "INSERT INTO `$tableName` VALUES (");
					$arr = $row;
					$buffer = "";
					self::$countRecords++;

	                foreach ($arr as $key => $value) {
						$value = mysqli_real_escape_string(self::$link, $value);
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

        if (self::$dbDropSyntax)
			fwrite($fd, "\nDROP table IF EXISTS `$tableName`;\n");

		$result = self::query("SHOW CREATE table `$databaseName`.`$tableName`;");
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
			self::error("Unable to open file $ftemp for writing");
			}

		return sprintf("%u", filesize($dumpfile));

	}

	public function resetcountRecords(){
		self::$countRecords = 0;

		return self::$countRecords;
	}

	public function getcountRecords(){
		return self::$countRecords;
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
		$return .= "# Mysql Compatibility: ". self::$dbCompatibility . "\n";

		$result = self::query("SHOW VARIABLES LIKE \"%version%\";");
		if($result){
			while($row = mysqli_fetch_array($result)){

					$return .= "# MYSQL ".$row[0].": ".$row[1]."\n";

				}
		}

		$return .= "#\n# Database : `" . $database . "`\n# --------------------------------------------------------\n\n";
		return $return;

	}


}
