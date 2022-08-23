<?php

namespace Watchfulli\XClonerCore;

use Exception;
use splitbrain\PHPArchive\ArchiveCorruptedException;
use splitbrain\PHPArchive\ArchiveIllegalCompressionException;
use splitbrain\PHPArchive\ArchiveIOException;
use splitbrain\PHPArchive\FileInfoException;

class Xcloner_Scheduler
{
    private $scheduler_table = "xcloner_scheduler";

    private $logger;
    private $xcloner_container;

    private $allowed_schedules = array("hourly", "twicedaily", "daily", "weekly", "monthly");

    public function __construct(Xcloner $xcloner_container)
    {
        $this->xcloner_container = $xcloner_container;

        $this->xcloner_container->get_xcloner_database()->show_errors = false;

        $this->scheduler_table = $this->xcloner_container->get_xcloner_database()->prefix . $this->scheduler_table;
    }

    public function get_scheduler_list($return_only_enabled = 0)
    {
        $list = $this->xcloner_container->get_xcloner_database()->get_results("SELECT * FROM " . $this->scheduler_table);

        if (!$list) {
            return array();
        }

        if ($return_only_enabled) {
            $new_list = array();

            foreach ($list as $res) {
                if ($res->status) {
                    $res->next_run_time = wp_next_scheduled('xcloner_scheduler_' . $res->id, array($res->id)) + ($this->xcloner_container->get_xcloner_settings()->get_xcloner_option('gmt_offset') * HOUR_IN_SECONDS);
                    if ($res->next_run_time) {
                        $new_list[] = $res;
                    }
                }
            }
            $list = $new_list;
        }

        return $list;
    }

    public function get_next_run_schedule()
    {
        return $this->get_scheduler_list($return_only_enabled = 1);
    }

    public function get_schedule_by_id_object($id)
    {
        $data = $this->xcloner_container->get_xcloner_database()->get_row("SELECT * FROM " . $this->scheduler_table . " WHERE id=" . $id);

        if (!$data) {
            return false;
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function get_schedule_by_id_or_name($id)
    {
        $data = $this->xcloner_container->get_xcloner_database()->get_row("SELECT * FROM " . $this->scheduler_table . " WHERE id='" . $id . "' or name='" . $id . "'", ARRAY_A);

        if (!$data) {
            throw new Exception("Schedule not found");
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function get_schedule_by_id($id)
    {
        $data = $this->xcloner_container->get_xcloner_database()->get_row("SELECT * FROM " . $this->scheduler_table . " WHERE id=" . $id, ARRAY_A);

        if (!$data) {
            throw new Exception("Schedule not found");
        }

        $params = json_decode($data['params']);

        $data['params'] = "";
        $data['backup_params'] = $params->backup_params;
        $data['table_params'] = json_encode($params->database);
        $data['excluded_files'] = json_encode($params->excluded_files);

        return $data;
    }

    public function delete_schedule_by_id($id)
    {
        $hook = 'xcloner_scheduler_' . $id;
        wp_clear_scheduled_hook($hook, array($id));

        return $this->xcloner_container->get_xcloner_database()->delete($this->scheduler_table, array('id' => $id));
    }

    public function deactivate_wp_cron_hooks()
    {
        $list = $this->get_scheduler_list();

        foreach ($list as $schedule) {
            $hook = 'xcloner_scheduler_' . $schedule->id;

            if ($timestamp = wp_next_scheduled($hook, array($schedule->id))) {
                wp_unschedule_event($timestamp, $hook, array($schedule->id));
            }
        }
    }

    public function update_wp_cron_hooks()
    {
        $list = $this->get_scheduler_list();

        foreach ($list as $schedule) {
            $hook = 'xcloner_scheduler_' . $schedule->id;

            //adding the xcloner_scheduler hook with xcloner_scheduler_callback callback
            $this->xcloner_container->get_xcloner_loader()->add_action($hook, [$this, 'xcloner_scheduler_callback']);

            if (!wp_next_scheduled($hook, array($schedule->id)) && $schedule->status) {
                if ($schedule->recurrence == "single") {
                    wp_schedule_single_event(strtotime($schedule->start_at), $hook, array($schedule->id));
                } else {
                    wp_schedule_event(strtotime($schedule->start_at), $schedule->recurrence, $hook, array($schedule->id));
                }
            } elseif (!$schedule->status) {
                if ($timestamp = wp_next_scheduled($hook, array($schedule->id))) {
                    wp_unschedule_event($timestamp, $hook, array($schedule->id));
                }
            }
        }
    }

    public function update_cron_hook($id)
    {
        $schedule = $this->get_schedule_by_id_object($id);
        $hook = 'xcloner_scheduler_' . $schedule->id;

        if ($timestamp = wp_next_scheduled($hook, array($schedule->id))) {
            wp_unschedule_event($timestamp, $hook, array($schedule->id));
        }

        if ($schedule->status) {
            if ($schedule->recurrence == "single") {
                wp_schedule_single_event(strtotime($schedule->start_at), $hook, array($schedule->id));
            } else {
                wp_schedule_event(strtotime($schedule->start_at), $schedule->recurrence, $hook, array($schedule->id));
            }
        }
    }

    public function disable_single_cron($schedule_id)
    {
        $schedule = array();
        $hook = 'xcloner_scheduler_' . $schedule_id;

        if ($timestamp = wp_next_scheduled($hook, array($schedule_id))) {
            wp_unschedule_event($timestamp, $hook, array($schedule_id));
        }

        $schedule['status'] = 0;

        return $this->xcloner_container->get_xcloner_database()->update(
            $this->scheduler_table,
            $schedule,
            array('id' => $schedule_id),
            array(
                '%s',
                '%s'
            )
        );
    }

    public function update_hash($schedule_id, $hash)
    {
        $schedule = array();

        $schedule['hash'] = $hash;

        return $this->xcloner_container->get_xcloner_database()->update(
            $this->scheduler_table,
            $schedule,
            array('id' => $schedule_id),
            array(
                '%s',
                '%s'
            )
        );
    }

    public function update_last_backup($schedule_id, $last_backup)
    {
        $schedule = array();

        $this->logger->info(sprintf('Updating last backup %s for schedule id #%s', $last_backup, $schedule_id));

        $schedule['last_backup'] = $last_backup;

        return $this->xcloner_container->get_xcloner_database()->update(
            $this->scheduler_table,
            $schedule,
            array('id' => $schedule_id),
            array(
                '%s',
                '%s'
            )
        );
    }

    private function _xcloner_scheduler_callback($id, $schedule)
    {
        set_time_limit(0);

        $start_time = time();

        $return_encrypted = array();
        $return = array();
        $additional = array();

        if (!isset($schedule['id'])) {
            $schedule['id'] = 0;
        }

        if (!isset($schedule['name'])) {
            $schedule['name'] = 'standalone';
        }

        $this->logger = $this->xcloner_container->get_xcloner_logger()->withName("xcloner_scheduler");

        $this->xcloner_container->get_xcloner_database()->show_errors = false;

        $this->logger->info(sprintf("New schedule hash is %s", $this->xcloner_container->get_xcloner_settings()->get_hash()));

        if (isset($schedule['backup_params']->diff_start_date) && $schedule['backup_params']->diff_start_date) {
            $this->xcloner_container->get_xcloner_filesystem()->set_diff_timestamp_start($schedule['backup_params']->diff_start_date);
        }


        if ($schedule['recurrence'] == "single") {
            $this->disable_single_cron($schedule['id']);
        }

        if (!$schedule) {
            $this->logger->info(sprintf("Could not load schedule with id'%s'", $id), array("CRON"));

            return;
        }

        if (defined('DOING_CRON')) {
            $this->update_hash($schedule['id'], $this->xcloner_container->get_xcloner_settings()->generate_new_hash());
        }

        $this->logger->print_info(sprintf("Starting backup profile '%s'", $schedule['name']), array("CRON"));

        $this->xcloner_container->get_xcloner_filesystem()->set_excluded_files(json_decode($schedule['excluded_files']));

        $init = 1;
        $continue = 1;

        while ($continue) {
            $continue = $this->xcloner_container->get_xcloner_filesystem()->start_file_recursion($init);

            $init = 0;
        }

        $this->logger->print_info(sprintf("File scan finished"), array("CRON"));

        $this->logger->print_info(sprintf("Starting the database backup"), array("CRON"));

        $init = 1;
        $return['finished'] = 0;

        while (!$return['finished']) {
            $return = $this->xcloner_container->get_xcloner_database()->start_database_recursion((array)json_decode($schedule['table_params']), $return, $init);
            $init = 0;
        }

        $this->logger->print_info(sprintf("Database backup done"), array("CRON"));

        $this->logger->print_info(sprintf("Starting file archive process"), array("CRON"));

        $init = 0;
        $return['finished'] = 0;
        $return['extra'] = array();

        while (!$return['finished']) {
            $return = $this->xcloner_container->get_archive_system()->start_incremental_backup((array)$schedule['backup_params'], $return['extra'], $init);
            $init = 0;
        }
        $this->logger->print_info(sprintf("File archive process FINISHED."), array("CRON"));

        //getting the last backup archive file
        $return['extra']['backup_parent'] = $this->xcloner_container->get_archive_system()->get_archive_name_with_extension();
        if ($this->xcloner_container->get_xcloner_filesystem()->is_part($this->xcloner_container->get_archive_system()->get_archive_name_with_extension())) {
            $return['extra']['backup_parent'] = $this->xcloner_container->get_archive_system()->get_archive_name_multipart();
        }

        //Updating schedule last backup archive
        $this->update_last_backup($schedule['id'], $return['extra']['backup_parent']);

        //Encrypting the backup archive
        $return_encrypted['finished'] = 0;
        $return_encrypted['start'] = 0;
        $return_encrypted['iv'] = '';
        $return_encrypted['target_file'] = '';
        $part = 0;
        $backup_parts = array();

        if (isset($schedule['backup_params']->backup_encrypt) && $schedule['backup_params']->backup_encrypt) {
            $this->logger->print_info(sprintf("Encrypting backup archive %s.", $return['extra']['backup_parent']), array("CRON"));

            $backup_file = $return['extra']['backup_parent'];

            if ($this->xcloner_container->get_xcloner_filesystem()->is_multipart($return['extra']['backup_parent'])) {
                $backup_parts = $this->xcloner_container->get_xcloner_filesystem()->get_multipart_files($return['extra']['backup_parent']);
                $backup_file = $backup_parts[$part];
            }

            while (!$return_encrypted['finished']) {
                $return_encrypted = $this->xcloner_container->get_xcloner_encryption()->encrypt_file(
                    $backup_file,
                    "",
                    "",
                    "",
                    "",
                    true,
                    true
                );

                if ($return_encrypted['finished']) {
                    ++$part;

                    if ($part < sizeof($backup_parts)) {
                        $return_encrypted['finished'] = 0;
                        $backup_file = $backup_parts[$part];
                    }
                }
            }
        }

        $additional['backup_size'] = size_format($this->xcloner_container->get_xcloner_filesystem()->get_backup_size($return['extra']['backup_parent']));

        //Sending backup to remote storage
        if (isset($schedule['remote_storage']) && $schedule['remote_storage'] && array_key_exists($schedule['remote_storage'], $this->xcloner_container->get_xcloner_remote_storage()->get_available_storages())) {
            $backup_file = $return['extra']['backup_parent'];

            $this->logger->print_info(sprintf("Transferring backup to remote storage %s", strtoupper($schedule['remote_storage'])), array("CRON"));

            if (method_exists($this->xcloner_container->get_xcloner_remote_storage(), "upload_backup_to_storage")) {
                call_user_func_array(array(
                    $this->xcloner_container->get_xcloner_remote_storage(),
                    "upload_backup_to_storage"
                ), array($backup_file, $schedule['remote_storage'], $schedule['backup_params']->backup_delete_after_remote_transfer));
            }
        }

        //Sending email notification
        if (isset($schedule['backup_params']->email_notification) and $to = $schedule['backup_params']->email_notification) {
            try {
                $from = "";
                $additional['lines_total'] = $return['extra']['lines_total'];
                $subject = sprintf(__("%s - new backup generated %s"), $schedule['name'], $return['extra']['backup_parent']);

                $this->xcloner_container->get_archive_system()->send_notification($to, $from, $subject, $return['extra']['backup_parent'], $schedule, "", $additional);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        //Backup Storage Cleanup
        $this->xcloner_container->get_xcloner_filesystem()->backup_storage_cleanup();

        //Filesystem Cleanup
        $this->xcloner_container->get_xcloner_filesystem()->cleanup_tmp_directories();

        //Removing the tmp filesystem used for backup
        $this->xcloner_container->get_xcloner_filesystem()->remove_tmp_filesystem();

        $this->logger->print_info(sprintf("Profile '%s' finished in %d seconds.", $schedule['name'], time() - $start_time), array("CRON"));

        return $return;
    }

    /**
     * @throws ArchiveIOException
     * @throws ArchiveCorruptedException
     * @throws FileInfoException
     * @throws ArchiveIllegalCompressionException
     */
    public function xcloner_scheduler_callback($id, $schedule = "")
    {
        try {
            if ($id) {
                $schedule = $this->get_schedule_by_id($id);
            }

            if ($this->xcloner_container->get_xcloner_settings()->get_xcloner_option('xcloner_disable_email_notification')) {
                //we disable email notifications
                $schedule['backup_params']->email_notification = "";
            }

            $this->_xcloner_scheduler_callback($id, $schedule);
        } catch (Exception $e) {

            //send email to site admin if email notification is not set in the scheduler
            if (!isset($schedule['backup_params']->email_notification) || !$schedule['backup_params']->email_notification) {
                $schedule['backup_params']->email_notification = $this->xcloner_container->get_xcloner_settings()->get_xcloner_option('admin_email');
            }

            if (isset($schedule['backup_params']->email_notification) && $to = $schedule['backup_params']->email_notification) {
                $from = "";
                $this->xcloner_container->get_archive_system()->send_notification($to, $from, $schedule['name'] . " - backup error", "", "", $e->getMessage());
            }
        }
    }

    public function get_available_intervals()
    {
        $schedules = wp_get_schedules();
        $new_schedules = array();

        foreach ($schedules as $key => $row) {
            if (in_array($key, $this->allowed_schedules)) {
                $new_schedules[$key] = $row;
                $intervals[$key] = $row['interval'];
            }
        }

        array_multisort($intervals, SORT_ASC, $new_schedules);

        $new_schedules['profile'] = [
            'interval' => '-1',
            'display' => 'Manual Execution'
        ];

        return $new_schedules;
    }
}
