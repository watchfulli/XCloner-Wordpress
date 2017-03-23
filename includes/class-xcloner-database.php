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


class Xcloner_Database extends wpdb{


	public  $debug 						= 0;
	public  $recordsPerSession			= 10000;
	public  $dbCompatibility			= "";
	public  $dbDropSyntax				= 1;
	public  $countRecords				= 0;

	private  $link;
	private  $db_selected;
	private  $logger;
	private  $fs;

	private   $TEMP_DBPROCESS_FILE = ".database";
	private   $TEMP_DUMP_FILE = "database-backup.sql";
	
	public function __construct(Xcloner $xcloner_container, $wp_user="", $wp_pass="", $wp_db="", $wp_host="")
	{
		$this->logger 					= $xcloner_container->get_xcloner_logger()->withName("xcloner_database");
		$this->xcloner_settings 		= $xcloner_container->get_xcloner_settings();
		$this->fs 						= $xcloner_container->get_xcloner_filesystem();
		
		if($this->xcloner_settings->get_xcloner_option('xcloner_database_records_per_request'))
			$this->recordsPerSession		= $this->xcloner_settings->get_xcloner_option('xcloner_database_records_per_request');
		
		if(!$this->recordsPerSession)
			$this->recordsPerSession = 100;
		
		if(!$wp_user && !$wp_pass && !$wp_host && !$wp_db )
		{
			$wp_host 	= $this->xcloner_settings->get_db_hostname();
			$wp_user 	= $this->xcloner_settings->get_db_username();
			$wp_pass 	= $this->xcloner_settings->get_db_password();
			$wp_db 		= $this->xcloner_settings->get_db_database();
		}
		
		parent::__construct($wp_user, $wp_pass, $wp_db, $wp_host);
		
		//$this->use_mysqli = true;
	}
	/*
	 * Initialize the database connection
	 *
	 * name: init
	 * @param array $data {'dbHostname', 'dbUsername', 'dbPassword', 'dbDatabase'}
	 * @return
	 */
	public function init($data, $start = 0)
	{
		if($start and $this->fs->get_tmp_filesystem()->has($this->TEMP_DBPROCESS_FILE)){
				$this->fs->get_tmp_filesystem()->delete($this->TEMP_DBPROCESS_FILE);
		}
		
		$this->headers();
		
		$this->suppress_errors = true;
	}
	
	public function start_database_recursion($params, $extra_params, $init = 0)
	{
		$tables = array();
		$return['finished'] = 0;
		$return['stats'] = array(
				"total_records"=>0,
				"tables_count"=>0,
				"database_count"=>0,
		);
		
		if(!$this->xcloner_settings->get_enable_mysql_backup())
		{
			$return['finished'] = 1;
			return $return;
		}
		
		$this->logger->debug(__("Starting database backup process"));
		
		$this->init($params, $init);
		
		if($init)
		{
			$db_count = 0;
			
			if(isset($params['#']))
			{
				foreach($params['#'] as $database)
				{
					if(!isset($params[$database]) or !is_array($params[$database]))
						$params[$database] = array();
				}
				$db_count = -1;
			}
			
			if(isset($params) and is_array($params))
				foreach($params as $database=>$tables)
				{	
					if($database != "#")
					{
						$stats = $this->write_backup_process_list($database, $tables);	
						$return['stats']['tables_count'] 	+= $stats['tables_count'];
						$return['stats']['total_records'] 	+= $stats['total_records'];
					}
				}

			if(sizeof($params))
				$return['stats']['database_count'] = sizeof($params)+$db_count;
			else	
				$return['stats']['database_count'] = 0;
				
			return $return;
		}
		
		if(!isset($extra_params['startAtLine']))
			$extra_params['startAtLine'] = 0;
		if(!isset($extra_params['startAtRecord']))
			$extra_params['startAtRecord'] = 0;
		if(!isset($extra_params['dumpfile']))
			$extra_params['dumpfile'] = "";
		
		$return = $this->process_incremental($extra_params['startAtLine'], $extra_params['startAtRecord'], $extra_params['dumpfile']);
		
		return $return;
	}
	
	public function log($message = "")
	{
		
		if($message){
			$this->logger->info( $message, array(""));
		}else{	
			if($this->last_query)
				$this->logger->debug( $this->last_query, array(""));
			if($this->last_error)
				$this->logger->error( $this->last_error, array(""));
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
		$this->logger->error( $message, array(""));
		
		return;
	}

	/*
	 * Send some special headers after the connection is initialized
	 *
	 * name: headers
	 * @param
	 * @return
	 */
	private function headers()
	{
		$this->logger->debug(__("Setting mysql headers"));
		
		$this->query("SET SQL_QUOTE_SHOW_CREATE=1;");
		//$this->log();
		$this->query("SET sql_mode = 0;");
		//$this->log();
		
		$this->set_charset($this->dbh, 'utf8');
		//$this->log();
	

	}

	public function get_database_num_tables($database)
	{
		$this->logger->debug(sprintf(__("Getting number of tables in %s"), $database));
		
		$query = "show tables in `".$database."`";
		
		$res =  $this->get_results($query);
		$this->log();
			
		return count($res);
	}
	
	public function get_all_databases()
	{
		$this->logger->debug(("Getting all databases"));
		
		$query = "SHOW DATABASES;";
		
		$databases = $this->get_results($query);
		$this->log();
		
		$databases_list = array();
		
		$i = 0;
		
		$databases_list[$i]['name'] = $this->dbname;
		$databases_list[$i]['num_tables'] = $this->get_database_num_tables($this->dbname);
		$i++;
		
		if(is_array($databases))
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
	 * Returns an array of tables from a database and mark $excluded ones
	 *
	 * name: list_tables
	 * @param string $database
	 * @param array $include
	 * @param int $get_num_records
	 * @return array $tablesList
	 */
	public function list_tables($database = "", $included = array(), $get_num_records = 0)
	{
		$tablesList[0] = array( );
		$inc = 0;

		if(!$database)
			$database = $this->dbname;
		
		$this->logger->debug(sprintf(("Listing tables in %s database"), $database));
		
		$tables = $this->get_results("SHOW TABLES in `".$database."`");
		$this->log();

		foreach ($tables as $table){
			
			$table = array_values((array)$table)[0];
			
			$tablesList[$inc]['name'] = $table;
			$tablesList[$inc]['database'] = $database;

			if($get_num_records)
			{
				$records_num_result = $this->get_var("SELECT count(*) FROM `".$database."`.`".$table."`");
				$this->log();
					
				$tablesList[$inc]['records'] = $records_num_result;
			}
			
			$tablesList[$inc]['excluded'] = 0;
						
			if(sizeof($included) and is_array($included))
				if(!in_array($table, $included) )
				{
					$tablesList[$inc]['excluded'] = 1;
					$this->log(sprintf(__("Excluding table %s.%s from backup"), $table, $database));
				}
			$inc++;
        }

		return $tablesList;

	}

	public function write_backup_process_list($dbname, $incl_tables)
	{
		$return['total_records'] = 0;
		$return['tables_count'] = 0;
		
		$this->log(__("Preparing the database recursion file"));
		
		$tables = $this->list_tables($dbname, $incl_tables, 1);
		
		if($this->dbname != $dbname)
			$dumpfile = $dbname."-backup.sql";
		else
			$dumpfile = $this->TEMP_DUMP_FILE;
		
		$line = sprintf("###newdump###\t%s\t%s\n", $dbname, $dumpfile);
		$this->fs->get_tmp_filesystem_append()->write($this->TEMP_DBPROCESS_FILE, $line);
			
		// write this to the class and write to $TEMP_DBPROCESS_FILE file as database.table records
		foreach($tables as $key=>$table) 
		if($table!= "" and !$tables[$key]['excluded']){

			$line = sprintf("`%s`.`%s`\t%s\t%s\n", $dbname, $tables[$key]['name'], $tables[$key]['records'], $tables[$key]['excluded']);
			$this->fs->get_tmp_filesystem_append()->write($this->TEMP_DBPROCESS_FILE, $line);
			$return['tables_count']++;
			$return['total_records'] += $tables[$key]['records'];
		}

		$line = sprintf("###enddump###\t%s\t%s\n", $dbname, $dumpfile);
		$this->fs->get_tmp_filesystem_append()->write($this->TEMP_DBPROCESS_FILE, $line);
		
		return $return;
	}

	/*
	 * Returns the number of records from a table
	 *
	 * name: countRecords
	 * @param string $table - the source table
	 * @return int $count
	 */
	public function countRecords($table)
	{

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
	public function process_incremental($startAtLine= 0, $startAtRecord = 0, $dumpfile = "", $dbCompatibility= ""){

		$count = 0;
		$return['finished'] = 0;
		$lines = array();
		
		if($this->fs->get_tmp_filesystem()->has($this->TEMP_DBPROCESS_FILE))
			$lines = array_filter(explode("\n",$this->fs->get_tmp_filesystem()->read($this->TEMP_DBPROCESS_FILE)));
	
		foreach ($lines as $buffer){
			
			if($count == $startAtLine)
			{
	
				$tableInfo =explode("\t", $buffer);
				
				if($tableInfo[0] == "###newdump###"){
						// we create a new mysql dump file
						if($dumpfile != ""){
								// we finished a previous one and write the footers
								$return['dumpsize'] = $this->data_footers($dumpfile);
						}
	
						$dumpfile = $tableInfo[2];
						
						$this->log(sprintf(__("Starting new backup dump to file %s"), $dumpfile));
						
						$this->data_headers($dumpfile, $tableInfo[1]);
						$dumpfile = $tableInfo[2];
						$startAtLine++;
						$return['new_dump'] = 1;
						//break;
				}else{
						//we export the table
						if($tableInfo[0] == "###enddump###")
							$return['endDump'] = 1;
	
						//table is excluded
						if($tableInfo[2])
							continue;
							
						$next = $startAtRecord + $this->recordsPerSession;
						
						// $tableInfo[1] number of records in the table
						$table = explode("`.`", $tableInfo[0]);
						$tableName = str_replace("`", "", $table[1]);
						$databaseName = str_replace("`", "", $table[0]);

						//return something to the browser
						$return['databaseName'] 	= $databaseName;
						$return['tableName'] 		= $tableName;
						$return['totalRecords'] 	= $tableInfo[1];

						$processed_records = 0;
						
						if(trim($tableName) !=""  and !$tableInfo[2])
							$processed_records = $this->export_table($databaseName, $tableName, $startAtRecord, $this->recordsPerSession, $dumpfile);
						
						$return['processedRecords'] = $startAtRecord+$processed_records;
						
						if($next >= $tableInfo[1]) //we finished loading the records for next sessions, will go to the new record
						{
								$startAtLine ++;
								$startAtRecord = 0;
						}else{
								$startAtRecord = $startAtRecord + $this->recordsPerSession;
							}

						//$return['dbCompatibility'] 	= self::$dbCompatibility;
						
						$return['startAtLine']		= $startAtLine;
						$return['startAtRecord']	= $startAtRecord;
						$return['dumpfile']			= $dumpfile;
						$return['dumpsize']			= $this->fs->get_tmp_filesystem_append()->getSize($dumpfile);

						return $return;
						break;
	
						
					}
	
			}
	
			$count++;
	
	
		}
	
		//while is finished, lets go home...
		if($dumpfile != ""){
			// we finished a previous one and write the footers
			$return['dumpsize'] = $this->data_footers($dumpfile);
			$return['dumpfile'] = ($dumpfile);
		}
		$return['finished'] = 1;
		$return['startAtLine']	= $startAtLine;
		
		if($this->fs->get_tmp_filesystem()->has($this->TEMP_DBPROCESS_FILE))
			$this->fs->get_tmp_filesystem()->delete($this->TEMP_DBPROCESS_FILE);
		
		$this->logger->debug(sprintf(("Database backup finished!")));
		
		return $return;


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
	public function export_table($databaseName, $tableName, $start, $limit, $dumpfile)
	{
		$this->logger->debug(sprintf(("Exporting table  %s.%s data"), $databaseName, $tableName));
		
		$records = 0;
		
		if($start == 0)
			$this->dump_structure($databaseName, $tableName, $dumpfile);

		$start = intval($start);
		$limit = intval($limit);
		//exporting the table content now

		$query = "SELECT * from `$databaseName`.`$tableName` Limit $start, $limit ;";
		if($this->use_mysqli)
		{
			$result = mysqli_query($this->dbh, $query);
			$mysql_fetch_function = "mysqli_fetch_array";
		
		}else{
			$result = mysql_query($query, $this->dbh);
			$mysql_fetch_function = "mysql_fetch_array";
		}
		//$result = $this->get_results($query, ARRAY_N);
		//print_r($result); exit;
		
		if($result){
			while($row = $mysql_fetch_function($result, MYSQLI_ASSOC)){
					
					$this->fs->get_tmp_filesystem_append()->write($dumpfile, "INSERT INTO `$tableName` VALUES (");
					$arr = $row;
					$buffer = "";
					$this->countRecords++;

	                foreach ($arr as $key => $value) {
						$value = $this->_real_escape($value);
						$buffer .= "'".$value."', ";
					}
					$buffer = rtrim($buffer, ', ') . ");\n";
					$this->fs->get_tmp_filesystem_append()->write($dumpfile, $buffer);
					unset($buffer);
					
					$records++;

				}
		}
		
		$this->log(sprintf(__("Dumping %s records starting position %s from %s.%s table"), $records, $start, $databaseName, $tableName));
		
		return $records;

	}

	public function dump_structure($databaseName, $tableName ,$dumpfile)
	{
		$this->log(sprintf(__("Dumping the structure for %s.%s table"), $databaseName, $tableName));
		
		$line = ("\n#\n# Table structure for table `$tableName`\n#\n\n");
		$this->fs->get_tmp_filesystem_append()->write($dumpfile, $line);

        if ($this->dbDropSyntax)
        {
			$line = ("\nDROP table IF EXISTS `$tableName`;\n");
			$this->fs->get_tmp_filesystem_append()->write($dumpfile, $line);
		}

		//$result = mysqli_query($this->dbh,"SHOW CREATE table `$databaseName`.`$tableName`;");
		$result = $this->get_row("SHOW CREATE table `$databaseName`.`$tableName`;", ARRAY_N);
		if($result){
			//$row = mysqli_fetch_row( $result);
			$line = ($result[1].";\n");
			$this->fs->get_tmp_filesystem_append()->write($dumpfile, $line);
		}

		$line = ( "\n#\n# End Structure for table `$tableName`\n#\n\n");
		$line .=("#\n# Dumping data for table `$tableName`\n#\n\n");
		$this->fs->get_tmp_filesystem_append()->write($dumpfile, $line);
		
		return;

	}

	public function data_footers($dumpfile)
	{
		$this->logger->debug(sprintf(("Writing dump footers in file"), $dumpfile));
		// we finished the dump file, not return the size of it
		$this->fs->get_tmp_filesystem_append()->write($dumpfile, "\n#\n# Finished at: ".date("M j, Y \a\\t H:i")."\n#");
		$size = $this->fs->get_tmp_filesystem_append()->getSize($dumpfile);
		
		$metadata_dumpfile = $this->fs->get_tmp_filesystem()->getMetadata($dumpfile);
		
		//adding dump file to the included files list
		$this->fs->store_file($metadata_dumpfile, 'tmp_filesystem');
		
		return $size;

	}

	public function resetcountRecords(){
		
		$this->countRecords = 0;

		return $this->countRecords;
	
	}

	public function getcountRecords(){
		
		return $this->countRecords;
		
	}


	public function data_headers($file, $database)
	{
		$this->logger->debug(sprintf(("Writing dump header for %s database in file"), $database, $file));
		
		$return = "";

		$return .= "#\n";
		$return .= "# Powered by XCloner Site Backup\n";
		$return .= "# http://www.xcloner.com\n";
		$return .= "#\n";
		$return .= "# Host: " . get_site_url() . "\n";
		$return .= "# Generation Time: " . date("M j, Y \a\\t H:i") . "\n";
		$return .= "# PHP Version: " . phpversion() . "\n";
		$return .= "# Database Charset: ". $this->charset . "\n";
		
		$results = $this->get_results("SHOW VARIABLES LIKE \"%version%\";", ARRAY_N);
		if(isset($results)){
			foreach($results as $result){

					$return .= "# MYSQL ".$result[0].": ".$result[1]."\n";

				}
		}
		
		$results = $this->get_results("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
					FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$database."';");
		
		if(isset($results[0])){

			$return .= "# MYSQL DEFAULT_CHARACTER_SET_NAME: ".$results[0]->DEFAULT_CHARACTER_SET_NAME."\n";
			$return .= "# MYSQL SCHEMA_NAME: ".$results[0]->DEFAULT_COLLATION_NAME."\n";
		}

		$return .= "#\n# Database : `" . $database . "`\n# --------------------------------------------------------\n\n";
		
		$this->log(sprintf(__("Writing %s database dump headers"), $database));
		
		$return = $this->fs->get_tmp_filesystem()->write($file, $return);
		return $return['size'];

	}


}
