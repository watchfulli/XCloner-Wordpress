<?php
namespace watchfulli\XClonerCore;

/**
 * XCloner - Backup and Restore backup plugin for Wordpress
 *
 * class-xcloner-api.php
 * @author Liuta Ovidiu <info@thinkovi.com>
 *
 *        This program is free software; you can redistribute it and/or modify
 *        it under the terms of the GNU General Public License as published by
 *        the Free Software Foundation; either version 2 of the License, or
 *        (at your option) any later version.
 *
 *        This program is distributed in the hope that it will be useful,
 *        but WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *        GNU General Public License for more details.
 *
 *        You should have received a copy of the GNU General Public License
 *        along with this program; if not, write to the Free Software
 *        Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *        MA 02110-1301, USA.
 *
 * @link https://github.com/ovidiul/XCloner-Wordpress
 *
 * @modified 7/31/18 3:00 PM
 *
 */

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
use League\Flysystem\Adapter\Local;

use splitbrain\PHPArchive\Tar;
use splitbrain\PHPArchive\Zip;
use splitbrain\PHPArchive\Archive;
use splitbrain\PHPArchive\FileInfo;

/**
 * XCloner Api Class
 */
class Xcloner_Api
{
    private $xcloner_database;
    private $xcloner_settings;
    private $xcloner_file_system;
    private $xcloner_scheduler;
    private $xcloner_requirements;
    private $xcloner_sanitization;
    private $xcloner_encryption;
    private $xcloner_remote_storage;
    private $archive_system;
    private $form_params;
    private $logger;
    private $xcloner_container;

    /**
     * XCloner_Api construct class
     *
     * @param Xcloner $xcloner_container [description]
     */
    public function __construct(Xcloner $xcloner_container)
    {
        //global $wpdb;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_reporting(0);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }
        ob_start();

        $this->xcloner_container = $xcloner_container;

        $this->xcloner_settings         = $xcloner_container->get_xcloner_settings();
        $this->logger                   = $xcloner_container->get_xcloner_logger()->withName("xcloner_api");
        $this->xcloner_file_system      = $xcloner_container->get_xcloner_filesystem();
        $this->xcloner_sanitization     = $xcloner_container->get_xcloner_sanitization();
        $this->xcloner_requirements     = $xcloner_container->get_xcloner_requirements();
        $this->archive_system           = $xcloner_container->get_archive_system();
        $this->xcloner_database         = $xcloner_container->get_xcloner_database();
        $this->xcloner_scheduler        = $xcloner_container->get_xcloner_scheduler();
        $this->xcloner_encryption       = $xcloner_container->get_xcloner_encryption();
        $this->xcloner_remote_storage   = $xcloner_container->get_xcloner_remote_storage();

        $this->xcloner_database->show_errors = false;

        if (isset($_POST['API_ID'])) {
            $this->logger->info("Processing ajax request ID ".substr(
                $this->xcloner_sanitization->sanitize_input_as_string($_POST['API_ID']),
                0,
                15
            ));
        }
    }

    /**
     * Get XCloner Container
     * @return XCloner return the XCloner container
     */
    public function get_xcloner_container()
    {
        return $this->xcloner_container;
    }


    /**
     * Checks API access
     */
    public function check_access()
    {
        //preparing nonce verification
        if (function_exists('wp_verify_nonce')) {
            if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'xcloner-api-nonce')) {
                throw new \Error(json_encode("Invalid nonce, please try again by refreshing the page!"));
            }
        }

        if (function_exists('current_user_can') && !current_user_can('manage_options')) {
            throw new \Error(json_encode("Access not allowed!"));
        }
    }

    /**
     * Initialize the database connection
     */
    public function init_db()
    {
        return;
    }

    /*
     * Save Schedule API
     */
    public function save_schedule()
    {
        //global $wpdb;

        $this->check_access();

        $scheduler = $this->xcloner_scheduler;
        $params = array();
        $schedule = array();
        $response = array();

        if (isset($_POST['data'])) {
            $params = json_decode(stripslashes($_POST['data']));
        }

        $this->process_params($params);

        if ( isset( $_POST['id'] ) ) {
		
	    $_POST['id'] = $this->xcloner_sanitization->sanitize_input_as_int( $_POST['id'] );
            $this->form_params['backup_params']['backup_name'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['backup_name']);
            $this->form_params['backup_params']['email_notification'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['email_notification']);
            if ($_POST['diff_start_date']) {
                $this->form_params['backup_params']['diff_start_date'] = strtotime($this->xcloner_sanitization->sanitize_input_as_string($_POST['diff_start_date']));
            } else {
                $this->form_params['backup_params']['diff_start_date'] = "";
            }
            $this->form_params['backup_params']['schedule_name'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['schedule_name']);
            $this->form_params['backup_params']['backup_encrypt'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['backup_encrypt']);
            
            $this->form_params['backup_params']['start_at'] = strtotime($_POST['schedule_start_date']);
            $this->form_params['backup_params']['schedule_frequency'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['schedule_frequency']);
            $this->form_params['backup_params']['schedule_storage'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['schedule_storage']);
            if (!isset($_POST['backup_delete_after_remote_transfer'])) {
                $_POST['backup_delete_after_remote_transfer'] = 0;
            }
            $this->form_params['backup_params']['backup_delete_after_remote_transfer'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['backup_delete_after_remote_transfer']);
            
            $this->form_params['database'] = (stripslashes($this->xcloner_sanitization->sanitize_input_as_raw($_POST['table_params'])));
            $this->form_params['excluded_files'] = (stripslashes($this->xcloner_sanitization->sanitize_input_as_raw($_POST['excluded_files'])));

            //$this->form_params['backup_params']['backup_type'] = $this->xcloner_sanitization->sanitize_input_as_string($_POST['backup_type']);

            $tables = explode(PHP_EOL, $this->form_params['database']);
            $return = array();

            foreach ($tables as $table) {
                $table = str_replace("\r", "", $table);
                $data = explode(".", $table);
                if (isset($data[1])) {
                    $return[$data[0]][] = $data[1];
                }
            }

            $this->form_params['database'] = ($return);

            $excluded_files = explode(PHP_EOL, $this->form_params['excluded_files']);
            $return = array();

            foreach ($excluded_files as $file) {
                $file = str_replace("\r", "", $file);
                if ($file) {
                    $return[] = $file;
                }
            }

            $this->form_params['excluded_files'] = ($return);

            $schedule['start_at'] = $this->form_params['backup_params']['start_at'];

            if (!isset($_POST['status'])) {
                $schedule['status'] = 0;
            } else {
                $schedule['status'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['status']);
            }
        } else {
            $schedule['status'] = 1;
            $schedule['start_at'] = strtotime($this->form_params['backup_params']['schedule_start_date'].
                " ".$this->form_params['backup_params']['schedule_start_time']);

            if ($schedule['start_at'] <= time()) {
                $schedule['start_at'] = "";
            }

            //fixing table names assigment
            foreach ($this->form_params['database'] as $db=>$tables) {
                if ($db == "#") {
                    continue;
                }

                foreach ($tables as $key=>$table) {
                    //echo $this->form_params['database'][$db][$key];
                    $this->form_params['database'][$db][$key] = substr($table, strlen($db)+1);
                }
            }
        }

        if (!$schedule['start_at']) {
            $schedule['start_at'] = date('Y-m-d H:i:s', time());
        } else {
            $schedule['start_at'] = date(
                'Y-m-d H:i:s',
                $schedule['start_at'] - ($this->xcloner_settings->get_xcloner_option('gmt_offset') * HOUR_IN_SECONDS)
            );
        }

        $schedule['name'] = $this->form_params['backup_params']['schedule_name'];
        $schedule['recurrence'] = $this->form_params['backup_params']['schedule_frequency'];
        if (!isset($this->form_params['backup_params']['schedule_storage'])) {
            $this->form_params['backup_params']['schedule_storage'] = "";
        }
        $schedule['remote_storage'] = $this->form_params['backup_params']['schedule_storage'];
        //$schedule['backup_type'] = $this->form_params['backup_params']['backup_type'];
        
        $schedule['params'] = json_encode($this->form_params);

        if (!isset($_POST['id'])) {
            $this->xcloner_database->insert(
                $this->xcloner_settings->get_table_prefix().'xcloner_scheduler',
                $schedule,
                array(
                    '%s',
                    '%s'
                )
            );
        } else {
            $this->xcloner_database->update(
                $this->xcloner_settings->get_table_prefix().'xcloner_scheduler',
                $schedule,
                array('id' => $_POST['id']),
                array(
                    '%s',
                    '%s'
                )
            );
        }
        if (isset($_POST['id'])) {
            $scheduler->update_cron_hook($_POST['id']);
        }

        if ($this->xcloner_database->last_error) {
            $response['error'] = 1;
            $response['error_message'] = $this->xcloner_database->last_error/*."--".$this->xcloner_database->last_query*/
            ;
        }

        $scheduler->update_wp_cron_hooks();
        $response['finished'] = 1;

        $this->send_response($response);
    }

    /*
     *
     * Backup Files API
     *
     */
    public function backup_files()
    {
        $return = array();
        $additional = array();

        $this->check_access();

        $params = json_decode(stripslashes($_POST['data']));

        $init = (int)$_POST['init'];

        if ($params === null) {
            return $this->send_response('{"status":false,"msg":"The post_data parameter must be valid JSON"}');
        }

        $this->process_params($params);

        $return['finished'] = 1;

        //$return = $this->archive_system->start_incremental_backup($this->form_params['backup_params'], $this->form_params['extra'], $init);
        try {
            $return = $this->archive_system->start_incremental_backup(
                $this->form_params['backup_params'],
                $this->form_params['extra'],
                $init
            );
        } catch (Exception $e) {
            $return = array();
            $return['error'] = true;
            $return['status'] = 500;
            $return['error_message'] = $e->getMessage();

            return $this->send_response($return, $hash = 1);
        }

        if ($return['finished']) {
            $return['extra']['backup_parent'] = $this->archive_system->get_archive_name_with_extension();
            if ($this->xcloner_file_system->is_part($this->archive_system->get_archive_name_with_extension())) {
                $return['extra']['backup_parent'] = $this->archive_system->get_archive_name_multipart();
            }
        }

        $data = $return;

        //check if backup is finished
        if ($return['finished']) {
            if (isset($this->form_params['backup_params']['email_notification']) and $to = $this->form_params['backup_params']['email_notification']) {
                try {
                    $from = "";
                    $subject = "";
                    $additional['lines_total'] = $return['extra']['lines_total'];
                    $this->archive_system->send_notification(
                        $to,
                        $from,
                        $subject,
                        $return['extra']['backup_parent'],
                        $this->form_params,
                        "",
                        $additional
                    );
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
            $this->xcloner_file_system->remove_tmp_filesystem();
        }

        return $this->send_response($data, $hash = 1);
    }

    /*
     *
     * Backup Database API
     *
     */
    public function backup_database()
    {
        $data = array();

        $this->check_access();

        $params = json_decode(stripslashes($_POST['data']));

        $init = (int)$_POST['init'];

        if ($params === null) {
            return $this->send_response('{"status":false,"msg":"The post_data parameter must be valid JSON"}');
        }

        $this->process_params($params);

        //$xcloner_database = $this->init_db();
        $return = $this->xcloner_database->start_database_recursion(
            $this->form_params['database'],
            $this->form_params['extra'],
            $init
        );

        if (isset($return['error']) and $return['error']) {
            $data['finished'] = 1;
        } else {
            $data['finished'] = $return['finished'];
        }

        $data['extra'] = $return;

        return $this->send_response($data, $hash = 1);
    }

    /*
     *
     * Scan Filesystem API
     *
     */
    public function scan_filesystem()
    {
        $data = array();

        $this->check_access();

        $params = json_decode(stripslashes($_POST['data']));

        $init = (int)$_POST['init'];

        if ($params === null) {
            $this->send_response('{"status":false,"msg":"The post_data profile parameter must be valid JSON"}');
        }

        $this->process_params($params);

        $this->xcloner_file_system->set_excluded_files($this->form_params['excluded_files']);

        $return = $this->xcloner_file_system->start_file_recursion($init);

        $data["finished"] = !$return;
        $data["total_files_num"] = $this->xcloner_file_system->get_scanned_files_num();
        $data["last_logged_file"] = $this->xcloner_file_system->last_logged_file();
        $data["total_files_size"] = sprintf(
            "%.2f",
            $this->xcloner_file_system->get_scanned_files_total_size() / (1024 * 1024)
        );

        return $this->send_response($data, $hash = 1);
    }

    /*
     *
     * Process params sent by the user
     *
     */
    private function process_params($params)
    {
        if ($params && property_exists($params, 'processed') && $params->processed) {
            $this->form_params = json_decode(json_encode((array)$params), true);
            return;
        }
        if (isset($params->hash)) {
            $this->xcloner_settings->set_hash($params->hash);
        }

        $this->form_params['extra'] = array();
        $this->form_params['backup_params'] = array();

        $this->form_params['database'] = array();

        if (isset($params->backup_params)) {
            foreach ($params->backup_params as $param) {
                $this->form_params['backup_params'][$param->name] = $this->xcloner_sanitization->sanitize_input_as_string($param->value);
                $this->logger->debug("Adding form parameter ".$param->name.".".$param->value."\n", array(
                    'POST',
                    'fields filter'
                ));
            }
        }

        $this->form_params['database'] = array();

        if (isset($params->table_params)) {
            foreach ($params->table_params as $param) {
                $this->form_params['database'][$param->parent][] = $this->xcloner_sanitization->sanitize_input_as_raw($param->id);
                $this->logger->debug("Adding database filter ".$param->parent.".".$param->id."\n", array(
                    'POST',
                    'database filter'
                ));
            }
        }

        $this->form_params['excluded_files'] = array();
        if (isset($params->files_params)) {
            foreach ($params->files_params as $param) {
                $this->form_params['excluded_files'][] = $this->xcloner_sanitization->sanitize_input_as_relative_path($param->id);
            }

            $unique_exclude_files = array();

            foreach ($params->files_params as $key => $param) {
                if (!in_array($param->parent, $this->form_params['excluded_files'])) {
                    //$this->form_params['excluded_files'][] = $this->xcloner_sanitization->sanitize_input_as_relative_path($param->id);
                    $unique_exclude_files[] = $param->id;
                    $this->logger->debug("Adding file filter ".$param->id."\n", array(
                        'POST',
                        'exclude files filter'
                    ));
                }
            }
            $this->form_params['excluded_files'] = (array)$unique_exclude_files;
        }

        //$this->form_params['excluded_files'] =  array_merge($this->form_params['excluded_files'], $this->exclude_files_by_default);

        if (isset($params->extra)) {
            foreach ($params->extra as $key => $value) {
                $this->form_params['extra'][$key] = $this->xcloner_sanitization->sanitize_input_as_raw($value);
            }
        }

        if (isset($this->form_params['backup_params']['diff_start_date']) and $this->form_params['backup_params']['diff_start_date']) {
            $this->form_params['backup_params']['diff_start_date'] = strtotime($this->form_params['backup_params']['diff_start_date']);
            $this->xcloner_file_system->set_diff_timestamp_start($this->form_params['backup_params']['diff_start_date']);
        }

        return $this->xcloner_settings->get_hash();
    }

    /*
     *
     * Get file list for tree view API
     *
     */
    public function get_file_system_action()
    {
        $this->check_access();

        $folder = $this->xcloner_sanitization->sanitize_input_as_relative_path($_POST['id']);

        $data = array();

        if ($folder == "#") {
            $folder = "/";
            $data[] = array(
                'id' => $folder,
                'parent' => '#',
                'text' => $this->xcloner_settings->get_xcloner_start_path(),
                //'children' => true,
                'state' => array('selected' => false, 'opened' => true),
                'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/file-icon-root.png"
            );
        }

        try {
            $files = $this->xcloner_file_system->list_directory($folder);
        } catch (Exception $e) {
            print $e->getMessage();
            $this->logger->error($e->getMessage());

            return;
        }

        $type = array();
        foreach ($files as $key => $row) {
            $type[$key] = $row['type'];
        }
        array_multisort($type, SORT_ASC, $files);

        foreach ($files as $file) {
            $children = false;
            $text = $file['basename'];

            if ($file['type'] == "dir") {
                $children = true;
            } else {
                $text .= " (".$this->xcloner_requirements->file_format_size($file['size']).")";
            }

            if ($this->xcloner_file_system->is_excluded($file)) {
                $selected = true;
            } else {
                $selected = false;
            }

            $data[] = array(
                'id' => $file['path'],
                'parent' => $folder,
                'text' => $text,
                //'title' => "test",
                'children' => $children,
                'state' => array('selected' => $selected, 'opened' => false, "checkbox_disabled" => $selected),
                'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/file-icon-".strtolower(substr(
                    $file['type'],
                    0,
                    1
                )).".png"
            );
        }


        return $this->send_response($data, 0);
    }

    /*
     *
     * Get databases/tables list for frontend tree display API
     *
     */
    public function get_database_tables_action()
    {
        $this->check_access();

        $database = $this->xcloner_sanitization->sanitize_input_as_raw($_POST['id']);

        $data = array();

        $xcloner_backup_only_wp_tables = $this->xcloner_settings->get_xcloner_option('xcloner_backup_only_wp_tables');

        if ($database == "#") {
            try {
                $return = $this->xcloner_database->get_all_databases();
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }

            foreach ($return as $database) {
                if ($xcloner_backup_only_wp_tables and $database['name'] != $this->xcloner_settings->get_db_database()) {
                    continue;
                }

                $state = array();

                if ($database['name'] == $this->xcloner_settings->get_db_database()) {
                    $state['selected'] = true;
                    if ($database['num_tables'] < 25) {
                        $state['opened'] = false;
                    }
                }

                $data[] = array(
                    'id' => $database['name'],
                    'parent' => '#',
                    'text' => $database['name']." (".(int)$database['num_tables'].")",
                    'children' => true,
                    'state' => $state,
                    'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/database-icon.png"
                );
            }
        } else {
            try {
                $return = $this->xcloner_database->list_tables($database, "", 1);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }

            foreach ($return as $table) {
                $state = array();

                if ($xcloner_backup_only_wp_tables and !stristr(
                    $table['name'],
                    $this->xcloner_settings->get_table_prefix()
                )) {
                    continue;
                }

                if (isset($database['name']) and $database['name'] == $this->xcloner_settings->get_db_database()) {
                    $state = array('selected' => true);
                }

                $data[] = array(
                    'id' => $database.".".$table['name'],
                    'parent' => $database,
                    'text' => $table['name']." (".(int)$table['records'].")",
                    'children' => false,
                    'state' => $state,
                    'icon' => plugin_dir_url(dirname(__FILE__))."/admin/assets/table-icon.png"
                );
            }
        }

        return $this->send_response($data, 0);
    }

    /*
     *
     * Get schedule by id API
     *
     */
    public function get_schedule_by_id()
    {
        $this->check_access();

        $schedule_id = $this->xcloner_sanitization->sanitize_input_as_int($_GET['id']);
        $scheduler = $this->xcloner_scheduler;
        $data = $scheduler->get_schedule_by_id($schedule_id);

        $data['start_at'] = date(
            "Y-m-d H:i",
            strtotime($data['start_at']) + ($this->xcloner_settings->get_xcloner_option('gmt_offset') * HOUR_IN_SECONDS)
        );
        if (isset($data['backup_params']->diff_start_date) && $data['backup_params']->diff_start_date != "") {
            $data['backup_params']->diff_start_date = date("Y-m-d", ($data['backup_params']->diff_start_date));
        }

        return $this->send_response($data);
    }

    /*
     *
     * Get Schedule list API
     *
     */
    public function get_scheduler_list()
    {
        $return = array();

        $this->check_access();

        $scheduler = $this->xcloner_scheduler;
        $data = $scheduler->get_scheduler_list();
        $return['data'] = array();

        foreach ($data as $res) {
            $action = "<a href=\"#".$res->id."\" class=\"edit\" title='Edit'> <i class=\"material-icons \">edit</i></a>
					<a href=\"#" . $res->id."\" class=\"delete\" title='Delete'><i class=\"material-icons  \">delete</i></a>";
            if ($res->status) {
                $status = '<i class="material-icons active status">timer</i>';
            } else {
                $status = '<i class="material-icons status inactive">timer_off</i>';
            }

            $next_run_time = wp_next_scheduled('xcloner_scheduler_'.$res->id, array($res->id));

            $next_run = date($this->xcloner_settings->get_xcloner_option('date_format')." ".$this->xcloner_settings->get_xcloner_option('time_format'), $next_run_time);

            $remote_storage = $res->remote_storage;

            if (!$next_run_time >= time()) {
                $next_run = " ";
            }

            if (trim($next_run)) {
                $date_text = date(
                    $this->xcloner_settings->get_xcloner_option('date_format')." ".$this->xcloner_settings->get_xcloner_option('time_format'),
                    $next_run_time + ($this->xcloner_settings->get_xcloner_option('gmt_offset') * HOUR_IN_SECONDS)
                );

                if ($next_run_time >= time()) {
                    $next_run = "in ".human_time_diff($next_run_time, time());
                } else {
                    $next_run = __("executed", 'xcloner-backup-and-restore');
                }

                $next_run = "<a href='#' title='".$date_text."'>".$next_run."</a>";
                //$next_run .=" ($date_text)";
            }

            $backup_text = "";
            $backup_size = "";
            $backup_time = "";

            if ($res->last_backup) {
                if ($this->xcloner_file_system->get_storage_filesystem()->has($res->last_backup)) {
                    $metadata = $this->xcloner_file_system->get_storage_filesystem()->getMetadata($res->last_backup);
                    $backup_size = size_format($this->xcloner_file_system->get_backup_size($res->last_backup));
                    $backup_time = date(
                        $this->xcloner_settings->get_xcloner_option('date_format')." ".$this->xcloner_settings->get_xcloner_option('time_format'),
                        $metadata['timestamp'] + ($this->xcloner_settings->get_xcloner_option('gmt_offset') * HOUR_IN_SECONDS)
                    );
                }

                $backup_text = "<span title='".$backup_time."' class='shorten_string'>".$res->last_backup." (".$backup_size.")</span>";
            }

            $schedules = $this->xcloner_scheduler->get_available_intervals();

            if (isset($schedules[$res->recurrence])) {
                $res->recurrence = $schedules[$res->recurrence]['display'];
            }

            $return['data'][] = array(
                $res->id,
                $res->name,
                $res->recurrence, /*$res->start_at,*/
                $next_run,
                $remote_storage,
                $backup_text,
                $status,
                $action
            );
        }

        return $this->send_response($return, 0);
    }

    /*
     *
     * Delete Schedule by ID API
     *
     */
    public function delete_schedule_by_id()
    {
        $data = array();

        $this->check_access();

        $schedule_id = $this->xcloner_sanitization->sanitize_input_as_int($_GET['id']);
        $scheduler = $this->xcloner_scheduler;
        $data['finished'] = $scheduler->delete_schedule_by_id($schedule_id);

        return $this->send_response($data);
    }

    /*
     *
     * Delete backup by name from the storage path
     *
     */
    public function delete_backup_by_name()
    {
        $data = array();

        $this->check_access();

        $backup_name = $this->xcloner_sanitization->sanitize_input_as_string($_POST['name']);
        $storage_selection = $this->xcloner_sanitization->sanitize_input_as_string($_POST['storage_selection']);

        $data['finished'] = $this->xcloner_file_system->delete_backup_by_name($backup_name, $storage_selection);

        return $this->send_response($data);
    }

    /**
     *  API Incremental Backup Encryption Method
     */
    public function backup_encryption()
    {
        $this->check_access();

        $backup_parts = array();
        $return = array();


        if (isset($_POST['data'])) {
            $params = json_decode(stripslashes($_POST['data']));

            $this->process_params($params);
            $source_backup_file = $this->xcloner_sanitization->sanitize_input_as_string($this->form_params['extra']['backup_parent']);

            if (isset($this->form_params['extra']['start'])) {
                $start = $this->xcloner_sanitization->sanitize_input_as_int($this->form_params['extra']['start']);
            } else {
                $start = 0;
            }

            if (isset($this->form_params['extra']['iv'])) {
                $iv = $this->xcloner_sanitization->sanitize_input_as_raw($this->form_params['extra']['iv']);
            } else {
                $iv = "";
            }

            if (isset($this->form_params['extra']['part'])) {
                $return['part'] = (int)$this->xcloner_sanitization->sanitize_input_as_int($this->form_params['extra']['part']);
            } else {
                $return['part'] = 0;
            }
        } else {
            $source_backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
            $start = $this->xcloner_sanitization->sanitize_input_as_int($_POST['start']);
            $iv = $this->xcloner_sanitization->sanitize_input_as_raw($_POST['iv']);
            $return['part'] = (int)$this->xcloner_sanitization->sanitize_input_as_int($_POST['part']);
        }

        $backup_file = $source_backup_file;

        if ($this->xcloner_file_system->is_multipart($backup_file)) {
            $backup_parts = $this->xcloner_file_system->get_multipart_files($backup_file);
            $backup_file = $backup_parts[$return['part']];
        }

        $return['processing_file'] = $backup_file;
        $return['total_size'] = filesize($this->xcloner_settings->get_xcloner_store_path().DS.$backup_file);

        try {
            $this->logger->info(json_encode($_POST));
            $this->logger->info($iv);
            $return = array_merge(
                $return,
                $this->xcloner_encryption->encrypt_file($backup_file, "", "", $start, base64_decode($iv))
            );
        } catch (\Exception $e) {
            $return['error'] = true;
            $return['message'] = $e->getMessage();
            $return['error_message'] = $e->getMessage();
        }

        //echo strlen($return['iv']);exit;

        if (isset($return['finished']) && $return['finished']) {
            if ($this->xcloner_file_system->is_multipart($source_backup_file)) {
                $return['start'] = 0;

                ++$return['part'];

                if ($return['part'] < sizeof($backup_parts)) {
                    $return['finished'] = 0;
                }
            }
        }

        if (isset($_POST['data'])) {
            $return['extra'] = array_merge($this->form_params['extra'], $return);
        }

        $this->send_response($return, 0);
    }

    /**
     *  API Incremental Backup Decryption Method
     */
    public function backup_decryption()
    {
        $this->check_access();

        $backup_parts = array();
        $return = array();

        $source_backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
        $start = $this->xcloner_sanitization->sanitize_input_as_int($_POST['start']);
        $iv = $this->xcloner_sanitization->sanitize_input_as_raw($_POST['iv']);
        $decryption_key = $this->xcloner_sanitization->sanitize_input_as_raw($_POST['decryption_key']);
        ;
        $return['part'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['part']);

        $backup_file = $source_backup_file;

        if ($this->xcloner_file_system->is_multipart($backup_file)) {
            $backup_parts = $this->xcloner_file_system->get_multipart_files($backup_file);
            $backup_file = $backup_parts[$return['part']];
        }

        $return['processing_file'] = $backup_file;
        $return['total_size'] = filesize($this->xcloner_settings->get_xcloner_store_path().DS.$backup_file);

        try {
            $return = array_merge(
                $return,
                $this->xcloner_encryption->decrypt_file($backup_file, "", $decryption_key, $start, base64_decode($iv))
            );
        } catch (\Exception $e) {
            $return['error'] = true;
            $return['message'] = $e->getMessage();
        }

        if ($return['finished']) {
            if ($this->xcloner_file_system->is_multipart($source_backup_file)) {
                $return['start'] = 0;

                ++$return['part'];

                if ($return['part'] < sizeof($backup_parts)) {
                    $return['finished'] = 0;
                }
            }
        }

        $this->send_response($return, 0);
    }

    public function get_manage_backups_list()
    {
        $this->check_access();

        $return = array(
            "data" => array()
        );

        $storage_selection = "";

        if (isset($_GET['storage_selection']) and $_GET['storage_selection']) {
            $storage_selection = $this->xcloner_sanitization->sanitize_input_as_string($_GET['storage_selection']);
        }
        $available_storages = $this->xcloner_remote_storage->get_available_storages();

        try {
            $backup_list = $this->xcloner_file_system->get_backup_archives_list($storage_selection);
        } catch (Exception $e) {
            $this->send_response($return, 0);
            return;
        }

        $i = -1;
        foreach ($backup_list as $file_info):?>
            <?php
            if ($storage_selection == "gdrive") {
                $file_info['path'] = $file_info['filename'].".".$file_info['extension'];
            }
        $file_exists_on_local_storage = true;

        if ($storage_selection) {
            if (!$this->xcloner_file_system->get_storage_filesystem()->has($file_info['path'])) {
                $file_exists_on_local_storage = false;
            }
        } ?>
            <?php if (!isset($file_info['parent'])): ?>

                <?php ob_start(); ?>
                        <p>
                        <label for="checkbox_<?php echo ++$i ?>">
                            <input name="backup[]" value="<?php echo $file_info['basename'] ?>" type="checkbox"
                                   id="checkbox_<?php echo $i ?>">
                            <span>&nbsp;</span>
                        </label>
                        </p>
                <?php
                $return['data'][$i][] = ob_get_contents();
        ob_end_clean(); ?>

                <?php ob_start(); ?>
                        <span class=""><?php echo $file_info['path'] ?></span>
                        <?php if (!$file_exists_on_local_storage): ?>
                            <a href="#"
                               title="<?php echo __(
            "This backup archive does not exist on your local server. If you wish to retain local copies of your backup archives as well as remote copies, please disable local clean-up in the main XCloner settings and/or your scheduled backup profile settings.",
            "xcloner-backup-and-restore"
        ) ?>"><i
                                        class="material-icons backup_warning">warning</i></a>
                        <?php endif ?>
                        <?php
                        if (isset($file_info['childs']) and is_array($file_info['childs'])):
                            ?>
                            <a href="#" title="expand" class="expand-multipart add"><i
                                        class="material-icons">add</i></a>
                            <a href="#" title="collapse" class="expand-multipart remove"><i class="material-icons">remove</i></a>
                            <ul class="multipart">
                                <?php foreach ($file_info['childs'] as $child): ?>
                                    <li>
                                        <?php echo $child[0] ?> (<?php echo esc_html(size_format($child[2])) ?>)
                                        <?php
                                        $child_exists_on_local_storage = true;
        if ($storage_selection) {
            if (!$this->xcloner_file_system->get_storage_filesystem()->has($child[0])) {
                $child_exists_on_local_storage = false;
            }
        } ?>
                                        <?php if (!$child_exists_on_local_storage): ?>
                                            <a href="#"
                                               title="<?php echo __(
            "This backup archive does not exist on your local server. If you wish to retain local copies of your backup archives as well as remote copies, please disable local clean-up in the main XCloner settings and/or your scheduled backup profile settings.",
            "xcloner-backup-and-restore"
        ) ?>"><i
                                                        class="material-icons backup_warning">warning</i></a>
                                        <?php endif ?>
                                        <?php if (!$storage_selection) : ?>
                                            <a href="#<?php echo $child[0]; ?>" class="download"
                                               title="Download Backup"><i class="material-icons">file_download</i></a>

                                            <?php if ($this->xcloner_encryption->is_encrypted_file($child[0])) :?>
                                                <a href="#<?php echo $child[0] ?>" class="backup-decryption"
                                                   title="<?php echo __('Backup Decryption', 'xcloner-backup-and-restore') ?>">
                                                    <i class="material-icons">enhanced_encryption</i>
                                                </a>
                                            <?php else: ?>
                                                <a href="#<?php echo $child[0] ?>" class="list-backup-content"
                                                   title="<?php echo __(
            'List Backup Content',
            'xcloner-backup-and-restore'
        ) ?>"><i
                                                            class="material-icons">folder_open</i></a>

                                                <a href="#<?php echo $child[0] ?>" class="backup-encryption"
                                                   title="<?php echo __('Backup Encryption', 'xcloner-backup-and-restore') ?>">
                                                    <i class="material-icons">no_encryption</i>
                                                </a>
                                            <?php endif?>

                                        <?php elseif ($storage_selection != "gdrive" && !$this->xcloner_file_system->get_storage_filesystem()->has($child[0])): ?>
                                            <a href="#<?php echo $child[0] ?>" class="copy-remote-to-local"
                                               title="<?php echo __(
            'Push Backup To Local Storage',
            'xcloner-backup-and-restore'
        ) ?>"><i
                                                        class="material-icons">file_upload</i></a>
                                        <?php endif ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                <?php
                $return['data'][$i][] = ob_get_contents();
        ob_end_clean(); ?>
                    <?php ob_start(); ?>
                        <?php if (isset($file_info['timestamp'])) {
            echo date("Y-m-d H:i", $file_info['timestamp']);
        } ?>
                    <?php
                        $return['data'][$i][] = ob_get_contents();
        ob_end_clean(); ?>

                    <?php ob_start(); ?>
                        <?php echo esc_html(size_format($file_info['size'])) ?>
                    <?php
                        $return['data'][$i][] = ob_get_contents();
        ob_end_clean(); ?>

                    <?php ob_start(); ?>
                        
                            <a href="#<?php echo $file_info['basename']; ?>" class="download"
                               title="<?php echo __('Download Backup', 'xcloner-backup-and-restore') ?>"><i
                                        class="material-icons">file_download</i></a>
                        <?php if (!$storage_selection): ?>
                            <?php if (sizeof($available_storages)): ?>
                                <a href="#<?php echo $file_info['basename'] ?>" class="cloud-upload"
                                   title="<?php echo __(
            'Send Backup To Remote Storage',
            'xcloner-backup-and-restore'
        ) ?>"><i
                                            class="material-icons">swap_horiz</i></a>
                            <?php endif ?>
                            <?php
                            $basename = $file_info['basename'];
        if (isset($file_info['childs']) and sizeof($file_info['childs'])) {
            $basename = $file_info['childs'][0][0];
        } ?>
                            <?php if ($this->xcloner_encryption->is_encrypted_file($basename)) :?>
                                <a href="#<?php echo $file_info['basename'] ?>" class="backup-decryption"
                                   title="<?php echo __('Backup Decryption', 'xcloner-backup-and-restore') ?>">
                                    <i class="material-icons">enhanced_encryption</i>
                                </a>
                            <?php else: ?>
                                <a href="#<?php echo $file_info['basename'] ?>" class="list-backup-content"
                                    title="<?php echo __('List Backup Content', 'xcloner-backup-and-restore') ?>"><i
                                    class="material-icons">folder_open</i></a>

                                <a href="#<?php echo $file_info['basename'] ?>" class="backup-encryption"
                                   title="<?php echo __('Backup Encryption', 'xcloner-backup-and-restore') ?>">
                                    <i class="material-icons">no_encryption</i>
                                </a>
                            <?php endif?>
                        <?php endif; ?>

                        <a href="#<?php echo $file_info['basename'] ?>" class="delete"
                           title="<?php echo __('Delete Backup', 'xcloner-backup-and-restore') ?>">
                            <i class="material-icons">delete</i>
                        </a>
                        <?php if ($storage_selection and !$file_exists_on_local_storage): ?>
                            <a href="#<?php echo $file_info['basename']; ?>" class="copy-remote-to-local"
                               title="<?php echo __('Transfer a copy of the remote backup to local storage.', 'xcloner-backup-and-restore') ?>"><i
                                        class="material-icons">swap_horiz</i></a>
                        <?php endif ?>

                    <?php
                        $return['data'][$i][] = ob_get_contents();
        ob_end_clean(); ?>

            <?php endif ?>
        <?php endforeach ?>
    <?php
        $this->send_response($return, 0);
    }

    /**
     * API method to list internal backup files
     */
    public function list_backup_files()
    {
        $this->check_access();

        $backup_parts = array();
        $return = array();

        $source_backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
        
        $start = $this->xcloner_sanitization->sanitize_input_as_int($_POST['start']);
        $return['part'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['part']);

        $backup_file = $source_backup_file;

        if ($this->xcloner_file_system->is_multipart($backup_file)) {
            $backup_parts = $this->xcloner_file_system->get_multipart_files($backup_file);
            $backup_file = $backup_parts[$return['part']];
        }

        if ($this->xcloner_encryption->is_encrypted_file($backup_file)) {
            $return['error'] = true;
            $return['message'] = __("Backup archive is encrypted, please decrypt it first before you can list it's content.", "xcloner-backup-and-restore");
            $this->send_response($return, 0);
        }

        try {
            $tar = $this->archive_system;
            $tar->open($this->xcloner_settings->get_xcloner_store_path().DS.$backup_file, $start);

            $data = $tar->contents($this->xcloner_settings->get_xcloner_option('xcloner_files_to_process_per_request'));
        } catch (Exception $e) {
            $return['error'] = true;
            $return['message'] = $e->getMessage();
            $this->send_response($return, 0);
        }

        $return['files'] = array();
        $return['finished'] = 1;
        $return['total_size'] = filesize($this->xcloner_settings->get_xcloner_store_path().DS.$backup_file);
        $i = 0;

        if (isset($data['extracted_files']) and is_array($data['extracted_files'])) {
            foreach ($data['extracted_files'] as $file) {
                $return['files'][$i]['path'] = $file->getPath();
                $return['files'][$i]['size'] = $file->getSize();
                $return['files'][$i]['mtime'] = date(
                    $this->xcloner_settings->get_xcloner_option('date_format')." ".$this->xcloner_settings->get_xcloner_option('time_format'),
                    $file->getMtime()
                );

                $i++;
            }
        }

        if (isset($data['start'])) {
            $return['start'] = $data['start'];
            $return['finished'] = 0;
        } else {
            if ($this->xcloner_file_system->is_multipart($source_backup_file)) {
                $return['start'] = 0;

                ++$return['part'];

                if ($return['part'] < sizeof($backup_parts)) {
                    $return['finished'] = 0;
                }
            }
        }

        $this->send_response($return, 0);
    }

    /*
     * Copy remote backup to local storage
     */
    public function copy_backup_remote_to_local()
    {
        $this->check_access();

        $backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
        $storage_type = $this->xcloner_sanitization->sanitize_input_as_string($_POST['storage_type']);

        $xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();

        $return = array();

        try {
            if (method_exists($xcloner_remote_storage, "copy_backup_remote_to_local")) {
                $return = call_user_func_array(array(
                    $xcloner_remote_storage,
                    "copy_backup_remote_to_local"
                ), array($backup_file, $storage_type));
            }
        } catch (Exception $e) {
            $return['error'] = 1;
            $return['message'] = $e->getMessage();
        }

        if (!$return) {
            $return['error'] = 1;
            $return['message'] = "Upload failed, please check the error log for more information!";
        }


        $this->send_response($return, 0);
    }

    /*
     *
     * Upload backup to remote API
     *
     */
    public function upload_backup_to_remote()
    {
        $this->check_access();

        $return = array();

        if (isset($_POST['data']) && $data = json_decode(stripslashes($_POST['data']), true)) {
            $backup_file = $this->xcloner_sanitization->sanitize_input_as_string($data['file']);
            $storage_type = $this->xcloner_sanitization->sanitize_input_as_string($data['storage_type']);
            $delete_local_copy_after_transfer = $this->xcloner_sanitization->sanitize_input_as_string($data['delete_after_transfer']);
        } else {
            $backup_file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
            $storage_type = $this->xcloner_sanitization->sanitize_input_as_string($_POST['storage_type']);
            $delete_local_copy_after_transfer = $this->xcloner_sanitization->sanitize_input_as_string($_POST['delete_after_transfer']);
        }
        $xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();

        try {
            if (method_exists($xcloner_remote_storage, "upload_backup_to_storage")) {
                $return = call_user_func_array(array(
                    $xcloner_remote_storage,
                    "upload_backup_to_storage"
                ), array($backup_file, $storage_type, $delete_local_copy_after_transfer));
            }
        } catch (Exception $e) {
            $return['error'] = 1;
            $return['message'] = $e->getMessage();
        }

        if (!$return) {
            $return['error'] = 1;
            $return['message'] = "Upload failed, please check the error log for more information!";
        }


        $this->send_response($return, 0);
    }

    /*
     *
     * Remote Storage Status Save
     *
     */
    public function remote_storage_save_status()
    {
        $this->check_access();

        $return = array();

        $xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();

        $return['finished'] = $xcloner_remote_storage->change_storage_status($_POST['id'], $_POST['value']);

        $this->send_response($return, 0);
    }


    public function download_restore_script()
    {
        $this->check_access();

        ob_end_clean();

        $adapter = new Local(dirname(__DIR__), LOCK_EX, '0001');
        $xcloner_plugin_filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        /* Generate PHAR FILE
        $file = 'restore/vendor.built';

        if(file_exists($file))
            unlink($file);
        $phar2 = new Phar($file, 0, 'vendor.phar');

        // add all files in the project, only include php files
        $phar2->buildFromIterator(
            new RecursiveIteratorIterator(
             new RecursiveDirectoryIterator(__DIR__.'/vendor/')),
            __DIR__);

        $phar2->setStub($phar2->createDefaultStub('vendor/autoload.php', 'vendor/autoload.php'));
         * */

        $tmp_file = $this->xcloner_settings->get_xcloner_tmp_path().DS."xcloner-restore.tgz";

        $tar = $this->archive_system;
        $tar->create($tmp_file);
        
        $vendor_phar_file = __DIR__."/../../../../restore/vendor.build.txt";
        if (!file_exists($vendor_phar_file)) {
            $vendor_phar_file = __DIR__."/../../restore/vendor.build.txt";
        }

        $tar->addFile($vendor_phar_file, "vendor.phar");
        
        //$tar->addFile(dirname(__DIR__)."/restore/vendor.tgz", "vendor.tgz");

        $files = $xcloner_plugin_filesystem->listContents("vendor/", true);
        foreach ($files as $file) {
            $tar->addFile(dirname(__DIR__).DS.$file['path'], $file['path']);
        }

        $xcloner_restore_file = (__DIR__."/../../../../restore/xcloner_restore.php");
        if (!file_exists($xcloner_restore_file)) {
            $xcloner_restore_file = (__DIR__."/../../restore/xcloner_restore.php");
        }

        $content = file_get_contents($xcloner_restore_file);
        $content = str_replace("define('AUTH_KEY', '');", "define('AUTH_KEY', '".md5(AUTH_KEY)."');", $content);

        $tar->addData("xcloner_restore.php", $content);

        $tar->close();

        if (file_exists($tmp_file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($tmp_file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.filesize($tmp_file));
            readfile($tmp_file);
        }

        try {
            unlink($tmp_file);
        } catch (Exception $e) {
            //We are not interested in the error here
        }

        die();
    }

    /*
     *
     * Download backup by Name from the Storage Path
     *
     */
    public function download_backup_by_name()
    {
        $this->check_access();

        ob_end_clean();

        $backup_name = $this->xcloner_sanitization->sanitize_input_as_string($_GET['name']);
        $storage_selection = $this->xcloner_sanitization->sanitize_input_as_string($_GET['storage_selection']);

        if (!$this->xcloner_file_system->get_storage_filesystem($storage_selection)->has($backup_name)) {
            die();
        }

        $metadata = $this->xcloner_file_system->get_storage_filesystem($storage_selection)->getMetadata($backup_name);
        $read_stream = $this->xcloner_file_system->get_storage_filesystem($storage_selection)->readStream($backup_name);

        $backup_name_export = $backup_name;

        if ($metadata['name']) {
            $backup_name_export = $metadata['name'];
        } elseif ($metadata['path']) {
            $backup_name_export = $metadata['path'];
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="'.$backup_name_export.'";');
        header('Content-Type: application/octet-stream');
        header('Content-Length: '.$metadata['size']);

        ob_end_clean();

        $chunkSize = 1024 * 1024;
        while (!feof($read_stream)) {
            $buffer = fread($read_stream, $chunkSize);
            echo $buffer;
        }
        fclose($read_stream);

        wp_die();
    }

    /*
     * Restore upload backup
     */
    public function restore_upload_backup()
    {
        $this->check_access();

        $return = array();

        $return['part'] = 0;
        $return['total_parts'] = 0;
        $return['uploaded_size'] = 0;
        $is_multipart = 0;

        $file = $this->xcloner_sanitization->sanitize_input_as_string($_POST['file']);
        $hash = $this->xcloner_sanitization->sanitize_input_as_string($_POST['hash']);

        if (isset($_POST['part'])) {
            $return['part'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['part']);
        }

        if (isset($_POST['uploaded_size'])) {
            $return['uploaded_size'] = $this->xcloner_sanitization->sanitize_input_as_int($_POST['uploaded_size']);
        }

        $start = $this->xcloner_sanitization->sanitize_input_as_string($_POST['start']);
        $target_url = $this->xcloner_sanitization->sanitize_input_as_string($_POST['target_url']);

        $return['total_size'] = $this->xcloner_file_system->get_backup_size($file);

        if ($this->xcloner_file_system->is_multipart($file)) {
            $backup_parts = $this->xcloner_file_system->get_multipart_files($file);

            $return['total_parts'] = sizeof($backup_parts) + 1;

            if ($return['part'] and isset($backup_parts[$return['part'] - 1])) {
                $file = $backup_parts[$return['part'] - 1];
            }

            $is_multipart = 1;
        }

        try {
            $xcloner_file_transfer = $this->get_xcloner_container()->get_xcloner_file_transfer();
            $xcloner_file_transfer->set_target($target_url);
            $return['start'] = $xcloner_file_transfer->transfer_file($file, $start, $hash);
        } catch (Exception $e) {
            $return = array();
            $return['error'] = true;
            $return['status'] = 500;
            $return['message'] = "CURL communication error with the restore host. ".$e->getMessage();
            $this->send_response($return, 0);
        }

        $return['status'] = 200;

        //we have finished the upload
        if (!$return['start'] and $is_multipart) {
            $return['part']++;
            $return['uploaded_size'] += $this->xcloner_file_system->get_storage_filesystem()->getSize($file);
        }

        $this->send_response($return, 0);
    }

    /*
     * Restore backup
     */
    public function restore_backup()
    {
        $this->check_access();

        define("XCLONER_PLUGIN_ACCESS", 1);
        include_once(dirname(__DIR__).DS."restore".DS."xcloner_restore.php");

        return;
    }

    /*
     *
     * Send the json response back
     *
     */
    private function send_response($data, $attach_hash = 1)
    {
        if ($attach_hash and null !== $this->xcloner_settings->get_hash()) {
            $data['hash'] = $this->xcloner_settings->get_hash();
        }

        /*
        if(!isset($data['_wpnonce']) && function_exists('wp_create_nonce')) {
            $data['_wpnonce'] = wp_create_nonce('xcloner-api-nonce');
        }*/

        if (ob_get_length()) {
            //ob_clean();
        }
        
        return wp_send_json($data);
    }
}
