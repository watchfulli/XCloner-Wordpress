<?php

namespace Watchfulli\XClonerCore;

use Exception;
use League\Flysystem\Config;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use splitbrain\PHPArchive\ArchiveCorruptedException;
use splitbrain\PHPArchive\ArchiveIllegalCompressionException;
use splitbrain\PHPArchive\ArchiveIOException;

class Xcloner_Restore
{
    private $backup_archive_extensions = array("zip", "tar", "tgz", "tar.gz", "gz", "csv");
    private $process_files_limit = 1500;
    private $process_mysql_records_limit = 250;
    private $site_path;

    /** @var Xcloner */
    private $xcloner_container;
    private $xcloner_sanitization;
    /** @var Local */
    private $adapter;
    /** @var Filesystem */
    private $filesystem;
    /** @var Logger */
    private $logger;

    /** @var string */
    private $backup_storage_dir;

    private $target_adapter;
    private $target_filesystem;

    /**
     * Xcloner_Restore constructor.
     * @throws Exception
     */
    public function __construct(Xcloner $xcloner_container)
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $this->site_path = realpath(get_home_path());

        $this->xcloner_container = $xcloner_container;
        $this->backup_storage_dir = $xcloner_container->get_xcloner_settings()->get_xcloner_store_path();
        $this->xcloner_sanitization = $xcloner_container->get_xcloner_sanitization();

        $this->logger = new Logger('xcloner_restore');
        $logger_path = $this->get_logger_filename();


        if (!@is_writeable($logger_path) && !@is_writable($this->backup_storage_dir)) {
            $logger_path = "php://stderr";
        }

        $this->logger->pushHandler(new StreamHandler($logger_path, Logger::DEBUG));

        $this->adapter = new Local($this->backup_storage_dir, LOCK_EX, 'SKIP_LINKS');
        $this->filesystem = new Filesystem($this->adapter, new Config([
            'disable_asserts' => true,
        ]));

        if (isset($_POST['API_ID'])) {
            $request_id = $this->xcloner_sanitization->sanitize_input_as_string($_POST['API_ID']);
            $this->logger->info("Processing ajax request ID " . $request_id);
        }
    }

    /**
     * Exception handler method
     */
    public function exception_handler()
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        if ($error['type'] && $this->logger) {
            $this->logger->info($this->friendly_error_type($error['type']) . ": " . var_export($error, true));
        }
    }

    /**
     * @param $type
     * @return mixed|string
     */
    private function friendly_error_type($type)
    {
        static $levels = null;
        if ($levels === null) {
            $levels = [];
            foreach (get_defined_constants() as $key => $value) {
                if (strpos($key, 'E_') !== 0) {
                    continue;
                }
                $levels[$value] = $key; //substr($key,2);
            }
        }
        return ($levels[$type] ?? "Error #{$type}");
    }

    public function get_logger_filename()
    {
        return $this->backup_storage_dir . DS . "xcloner_restore.log";
    }

    /**
     * Init method
     *
     * @throws Exception
     */
    public function init()
    {
        $this->check_system();
    }

    /**
     * @throws Exception
     */
    public function execute_action($action)
    {
        register_shutdown_function(array($this, 'exception_handler'));

        $method = $action . "_action";

        if (!method_exists($this, $method) && !method_exists($this->xcloner_container->get_xcloner_api(), $action)) {
            throw new Exception($method . " does not exists");
        }

        $this->logger->debug(sprintf('Starting action %s', $method));

        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method));
        }

        return call_user_func(array($this->xcloner_container->get_xcloner_api(), $action));
    }

    /**
     * Write file method
     *
     * @throws Exception
     */
    public function write_file_action()
    {
        $target_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);

        if (empty($target_file)) {
            throw new Exception('Empty file name');
        }

        $start_position = $this->xcloner_sanitization->sanitize_input_as_int($_POST['start']);

        if (!$start_position) {
            $fp = fopen($target_file, "wb+");
        } else {
            $fp = fopen($target_file, "ab+");
        }

        if (!$fp) {
            throw new Exception('Unable to open $target_file file for writing');
        }

        fseek($fp, $start_position);

        if (isset($_FILES['blob'])) {
            $this->logger->debug(sprintf('Writing %s bytes to file %s starting position %s using FILES blob', filesize($_FILES['blob']['tmp_name']), $target_file, $start_position));

            $blob = file_get_contents($_FILES['blob']['tmp_name']);

            if (!$bytes_written = fwrite($fp, $blob)) {
                throw new Exception("Unable to write data to file $target_file");
            }

            @unlink($_FILES['blob']['tmp_name']);
        } elseif (isset($_POST['blob'])) {
            $blob = $this->xcloner_sanitization->sanitize_input_as_string($_POST['blob']);
            $this->logger->debug(sprintf('Writing %s bytes to file %s starting position %s using POST blob', strlen($blob), $target_file, $start_position));

            if (!$bytes_written = fwrite($fp, $blob)) {
                throw new Exception("Unable to write data to file $target_file");
            }
        } else {
            throw new Exception("Upload failed, did not receive any binary data");
        }

        fclose($fp);

        return $bytes_written;
    }

    /**
     * Restore mysql backup file
     *
     * @throws Exception
     */
    public function restore_mysql_backup_action()
    {
        $mysqldump_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['mysqldump_file']);

        $execute_query = $this->xcloner_sanitization->sanitize_input_as_string(trim(stripslashes($_POST['query'])));
        $start = $this->xcloner_sanitization->sanitize_input_as_int($_POST['start']);

        $mysql_backup_file = $this->site_path . DS . $mysqldump_file;

        if (!file_exists($mysql_backup_file)) {
            throw new Exception(sprintf("Mysql backup file %s does not exists", $mysql_backup_file));
        }

        $mysqli = $this->xcloner_container->get_xcloner_database();

        $line_count = 0;
        $query = "";
        $return = array();
        $return['finished'] = 1;
        $return['backup_file'] = $mysqldump_file;
        $return['backup_size'] = filesize($mysql_backup_file);

        $fp = fopen($mysql_backup_file, "r");
        if (!$fp) {
            $this->logger->error(sprintf('Unable to open mysql backup file %s', $mysql_backup_file));
            $this->send_response(200, $return);
        }

        $this->logger->info(sprintf("Opening mysql dump file %s at position %s.", $mysql_backup_file, $start));
        fseek($fp, $start);
        while ($line_count <= $this->process_mysql_records_limit and ($line = fgets($fp)) !== false) {
            // process the line read.

            //check if line is comment
            if (substr($line, 0, 1) == "#") {
                continue;
            }

            //check if line is empty
            if ($line == "\n" or trim($line) == "") {
                continue;
            }

            //exclude usermeta info for local restores to fix potential session logout
            if (strpos($line, "_usermeta`") !== false) {
                $line = str_replace("_usermeta`", "_usermeta2`", $line);
            }

            $query .= $line;
            if (substr($line, strlen($line) - 2, strlen($line)) != ";\n") {
                continue;
            }

            if ($execute_query) {
                $query = (($execute_query));
                $execute_query = "";
            }

            if (!$mysqli->query($query) && !stristr($mysqli->error, "Duplicate entry")) {
                //$return['error_line'] = $line_count;
                $return['start'] = ftell($fp) - strlen($line);
                $return['query_error'] = true;
                $return['query'] = $query;
                $return['message'] = sprintf("Mysql Error: %s\n", $mysqli->error);

                $this->logger->error($return['message']);

                $this->send_response(418, $return);
                //throw new Exception(sprintf("Mysql Error: %s\n Mysql Query: %s", $mysqli->error, $query));
            }
            //else echo $line;

            $query = "";

            $line_count++;
        }

        $return['start'] = ftell($fp);

        $this->logger->info(sprintf("Executed %s queries of size %s bytes", $line_count, ($return['start'] - $start)));

        if (!feof($fp)) {
            $return['finished'] = 0;
        } else {
            $this->logger->info('Mysql Import Done.');
        }

        fclose($fp);

        $this->send_response(200, $return);
    }

    /**
     * Finish backup restore method
     *
     * @throws FileNotFoundException
     */
    public function restore_finish_action()
    {
        $backup_archive = $this->xcloner_sanitization->sanitize_input_as_string($_POST['bakcup_archive']);

        $delete_backup_archive = $this->xcloner_sanitization->sanitize_input_as_int($_POST['delete_backup_archive']);
        $delete_backup_temporary_folder = $this->xcloner_sanitization->sanitize_input_as_int($_POST['delete_backup_temporary_folder']);

        if ($delete_backup_temporary_folder) {
            $this->delete_backup_temporary_folder();
        }

        if ($delete_backup_archive) {
            $this->filesystem->delete($backup_archive);
        }

        global $wpdb;

        $wpdb->select($wpdb->dbname);

        $wpdb->hide_errors();
        if ($result = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "usermeta2")) {
            if ($result > 0) {
                $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "usermeta_backup");
                $wpdb->query("ALTER TABLE " . $wpdb->prefix . "usermeta RENAME TO " . $wpdb->prefix . "usermeta_backup");
                $wpdb->query("ALTER TABLE " . $wpdb->prefix . "usermeta2 RENAME TO " . $wpdb->prefix . "usermeta");
            }
        }

        $return = "Restore Process Finished.";
        $this->send_response(200, $return);
    }

    /**
     * Delete backup temporary folder
     */
    private function delete_backup_temporary_folder()
    {
        $this->target_adapter = new Local($this->site_path, LOCK_EX, 'SKIP_LINKS');
        $this->target_filesystem = new Filesystem($this->target_adapter, new Config([
            'disable_asserts' => true,
        ]));

        $list = $this->target_filesystem->listContents();

        foreach ($list as $file) {
            $matches = array();

            if ($file['type'] == "dir") {
                if (preg_match("/xcloner-(\w*)/", $file['basename'], $matches)) {
                    $this->logger->info(sprintf('Deleting temporary folder %s', $file['path']));
                    $this->target_filesystem->deleteDir($file['path']);
                }
            }
        }
    }

    /**
     * List mysqldump database backup files
     *
     */
    public function list_mysqldump_backups_action()
    {
        $source_backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['backup_file']);

        $hash = $this->get_hash_from_backup($source_backup_file);

        $this->target_adapter = new Local($this->site_path, LOCK_EX, 'SKIP_LINKS');
        $this->target_filesystem = new Filesystem($this->target_adapter, new Config([
            'disable_asserts' => true,
        ]));

        $mysqldump_list = array();
        $list = $this->target_filesystem->listContents();

        foreach ($list as $file) {
            $matches = array();

            if ($file['type'] == "dir") {
                if (preg_match("/xcloner-(\w*)/", $file['basename'], $matches)) {
                    $contents = $this->target_filesystem->listContents($file['basename']);
                    foreach ($contents as $content) {
                        if ($content['extension'] == "sql") {
                            $this->logger->info(sprintf('Found %s mysql backup file', $content['path']));
                            $mysqldump_list[$content['path']]['path'] = $content['path'];
                            $mysqldump_list[$content['path']]['size'] = $content['size'];
                            $mysqldump_list[$content['path']]['timestamp'] = date("d M,Y H:i", $content['timestamp']);

                            if ($hash and $hash == $matches[1]) {
                                $mysqldump_list[$content['path']]['selected'] = "selected";
                            } else {
                                $mysqldump_list[$content['path']]['selected'] = "";
                            }
                        }
                    }
                }
            }
        }

        $this->sort_by($mysqldump_list, 'timestamp', 'desc');
        $return['files'] = $mysqldump_list;

        $this->send_response(200, $return);
    }

    /**
     * Get backup hash method
     *
     * @param $backup_file
     * @return false|string
     */
    private function get_hash_from_backup($backup_file)
    {
        if (!$backup_file) {
            return false;
        }

        $result = preg_match("/-(\w*)./", substr($backup_file, strlen($backup_file) - 10, strlen($backup_file)), $matches);

        if ($result and isset($matches[1])) {
            return ($matches[1]);
        }

        return false;
    }

    /**
     * List backup archives found on local system
     *
     * @throws FileNotFoundException
     */
    public function list_backup_archives_action()
    {
        $local_backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['local_backup_file']);

        $list = $this->filesystem->listContents();

        $backup_files = array();
        $parents = array();

        foreach ($list as $file_info) {
            if (!isset($file_info['extension'])) {
                continue;
            }
            if ($file_info['extension'] == "csv") {
                $lines = explode(PHP_EOL, $this->filesystem->read($file_info['path']));
                foreach ($lines as $line) {
                    if (!$line) {
                        continue;
                    }
                    $data = str_getcsv($line);
                    $parents[$data[0]] = $file_info['path'];
                    $file_info['childs'][] = $data;
                    $file_info['size'] += $data[2];
                }
            }

            if ($file_info['type'] == 'file' && in_array($file_info['extension'], $this->backup_archive_extensions)) {
                $backup_files[$file_info['path']] = $file_info;
            }
        }

        $backup_files = array_map(function ($file_info) use ($parents, $local_backup_file) {
            if (isset($parents[$file_info['path']])) {
                $file_info['parent'] = $parents[$file_info['path']];
            } else {
                if ($local_backup_file && ($file_info['basename'] == $local_backup_file)) {
                    $file_info['selected'] = 'selected';
                }
            }
            $file_info['file_size'] = $file_info['size'];
            return $file_info;
        }, $backup_files);

        $this->sort_by($backup_files, "timestamp", "desc");

        $return['files'] = $backup_files;

        $this->send_response(200, $return);
    }

    public function get_current_directory_action()
    {
        $return['dir'] = realpath(get_home_path());
        $return['restore_script_url'] = get_site_url();
        $return['remote_mysql_host'] 	= '';
        $return['remote_mysql_user'] 	= '';
        $return['remote_mysql_pass'] 	= '';
        $return['remote_mysql_db'] = '';

        $this->logger->info(sprintf('Determining current url as %s and path as %s', $return['dir'], $return['restore_script_url']));

        $this->send_response(200, $return);
    }

    /**
     * Restore backup archive to local path
     *
     * @throws FileNotFoundException
     * @throws ArchiveCorruptedException
     * @throws ArchiveIOException
     * @throws ArchiveIllegalCompressionException
     */
    public function restore_backup_to_path_action()
    {
        $source_backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['backup_file']);
        $include_filter_files = $this->xcloner_sanitization->sanitize_input_as_string($_POST['filter_files']);
        $exclude_filter_files = "";
        $start = $this->xcloner_sanitization->sanitize_input_as_int($_POST['start']);
        $return['part'] = (int)$this->xcloner_sanitization->sanitize_input_as_int($_POST['part']);
        $return['processed'] = (int)$this->xcloner_sanitization->sanitize_input_as_int($_POST['processed']);
        $this->target_adapter = new Local($this->site_path, LOCK_EX, 'SKIP_LINKS');
        $this->target_filesystem = new Filesystem($this->target_adapter, new Config([
            'disable_asserts' => true,
        ]));

        $backup_file = $source_backup_file;

        $return['finished'] = 1;
        $return['extracted_files'] = array();
        $return['total_size'] = $this->get_backup_size($backup_file);


        $backup_archive = new Xcloner_Archive_Restore();
        if ($this->is_multipart($backup_file)) {
            if (!$return['part']) {
                $return['processed'] += $this->filesystem->getSize($backup_file);
            }

            $backup_parts = $this->get_multipart_files($backup_file);
            $backup_file = $backup_parts[$return['part']];
        }

        if ($this->is_encrypted_file($backup_file)) {
            $message = sprintf('Backup file %s seems encrypted, please Decrypt it first from your Manage Backups panel.', $backup_file);
            $this->logger->error($message);
            $this->send_response(500, $message);
            return;
        }

        $this->logger->info(sprintf('Opening backup archive %s at position %s', $backup_file, $start));
        $backup_archive->open($this->backup_storage_dir . DS . $backup_file, $start);


        $data = $backup_archive->extract($this->site_path, '', $exclude_filter_files, $include_filter_files, $this->process_files_limit);
        if (isset($data['extracted_files'])) {
            foreach ($data['extracted_files'] as $spl_fileinfo) {
                $this->logger->info(sprintf('Extracted %s file', $spl_fileinfo->getPath()));
                $return['extracted_files'][] = $spl_fileinfo->getPath() . " (" . $spl_fileinfo->getSize() . " bytes)";
            }
        }


        if (isset($data['start'])) {
            //if(isset($data['start']) and $data['start'] <= $this->filesystem->getSize($backup_file))
            $return['finished'] = 0;
            $return['start'] = $data['start'];
        } else {
            $return['processed'] += $start;

            if ($this->is_multipart($source_backup_file)) {
                $return['start'] = 0;

                ++$return['part'];

                if ($return['part'] < sizeof($backup_parts)) {
                    $return['finished'] = 0;
                }
            }
        }

        if ($return['finished']) {
            $this->logger->info(sprintf('Done extracting %s', $source_backup_file));
        }

        $return['backup_file'] = $backup_file;

        $this->send_response(200, $return);
    }

    /**
     * Check if provided filename has encrypted suffix
     *
     * @param $filename
     * @return bool
     * @throws FileNotFoundException
     */
    public function is_encrypted_file($filename)
    {
        $fp = $this->filesystem->readStream($filename);
        if (is_resource($fp)) {
            $encryption_length = fread($fp, 16);
            fclose($fp);
            if (is_numeric($encryption_length)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check current filesystem
     *
     * @throws Exception
     */
    private function check_system()
    {
        //check if I can write
        $tmp_file = md5(time());
        if (!file_put_contents($tmp_file, "++")) {
            throw new Exception("Could not write to new host");
        }

        if (!unlink($tmp_file)) {
            throw new Exception("Could not delete temporary file from new host");
        }

        $max_upload = $this->return_bytes((ini_get('upload_max_filesize')));
        $max_post = $this->return_bytes((ini_get('post_max_size')));

        $return['max_upload_size'] = min($max_upload, $max_post); // bytes
        $return['status'] = true;

        $this->logger->info(sprintf('Current filesystem max upload size is %s bytes', $return['max_upload_size']));

        $this->send_response(200, $return);
    }

    /**
     * Return bytes from human-readable value
     *
     * @param string $val
     * @return int
     *
     */
    private function return_bytes($val)
    {
        $numeric_val = (int)trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                //gigabytes
                $numeric_val *= 1024;
            // no break
            case 'm':
                //megabytes
                $numeric_val *= 1024;
            // no break
            case 'k':
                //kilobytes
                $numeric_val *= 1024;
        }

        return $numeric_val;
    }

    /**
     * Check if backup archive os multipart
     *
     * @param $backup_name
     * @return bool
     */
    public function is_multipart($backup_name)
    {
        if (stristr($backup_name, "-multipart")) {
            return true;
        }

        return false;
    }

    /**
     * Get backup archive size
     *
     * @param $backup_name
     * @return bool|false|int
     * @throws FileNotFoundException
     */
    public function get_backup_size($backup_name)
    {
        $backup_size = $this->filesystem->getSize($backup_name);
        if ($this->is_multipart($backup_name)) {
            $backup_parts = $this->get_multipart_files($backup_name);
            foreach ($backup_parts as $part_file) {
                $backup_size += $this->filesystem->getSize($part_file);
            }
        }

        return $backup_size;
    }

    /**
     * Get multipart backup files list
     * @param $backup_name
     * @return array
     * @throws FileNotFoundException
     */
    public function get_multipart_files($backup_name)
    {
        $files = array();

        if ($this->is_multipart($backup_name)) {
            $lines = explode(PHP_EOL, $this->filesystem->read($backup_name));
            foreach ($lines as $line) {
                if ($line) {
                    $data = str_getcsv($line);
                    $files[] = $data[0];
                }
            }
        }

        return $files;
    }

    private function sort_by(&$array, $field, $direction = 'asc')
    {
        $direction = strtolower($direction);

        usort(
            $array,

            /**
             * @param string $b
             */
            function ($a, $b) use ($field, $direction) {
                $a = $a[$field];
                $b = $b[$field];

                if ($a == $b) {
                    return 0;
                }

                if ($direction == 'desc') {
                    if ($a > $b) {
                        return -1;
                    } else {
                        return 1;
                    }
                } else {
                    if ($a < $b) {
                        return -1;
                    } else {
                        return 1;
                    }
                }
            }
        );
    }


    /**
     * Send response method
     *
     * @param int $status
     * @param $response
     */
    public static function send_response($status, $response)
    {
        if (empty($status)) {
            $status = 200;
        }
        header("Access-Control-Allow-Origin: *");
        header("HTTP/1.1 200");
        header('Content-Type: application/json');
        $return['status'] = $status;
        $return['statusText'] = $response;

        if (isset($response['error']) && $response['error']) {
            $return['statusText'] = $response['message'];
            $return['error'] = true;
        } elseif ($status != 200 and $status != 418) {
            $return['error'] = true;
            $return['message'] = $response;
        }

        die(json_encode($return));
    }
}
