<?php

namespace Watchfulli\XClonerCore;

use Exception;
use splitbrain\PHPArchive\ArchiveIllegalCompressionException;
use splitbrain\PHPArchive\ArchiveIOException;

class Xcloner_cli
{
    private $argv;

    public function __construct($argv) {
        $this->argv = $argv;
    }

    public function shouldRun() {
        if (php_sapi_name() !== 'cli') {
            return false;
        }

        if (defined('WP_CLI') && WP_CLI === true) {
            return true;
        }

        if (isset($this->argv) && basename($this->argv[0]) === 'xcloner.php') {
            return true;
        }

        return false;
    }


    /**
     * @throws ArchiveIllegalCompressionException
     * @throws ArchiveIOException
     */
    public function run($argv = array(), $opts = array())
    {
        if (!$this->shouldRun()) {
            return;
        }

        if (defined('WP_CLI') && WP_CLI === true) {
            $this->do_wp_cli_execution();
            return;
        }

        $this->do_cli_execution($argv);
    }

    private function do_wp_cli_execution()
    {
        \WP_CLI::add_command(
            'xcloner_generate_backup',
            /**
             * XCloner Generate backup based on supplied profile Name or ID
             *
             * [--profile=<profile>]
             * : backup profile name or id
             *
             * [--encrypt=<backup_name>]
             * : encrypt backup archive
             *
             * [--decrypt=<backup_name>]
             * : decrypt backup archive
             *
             * [--key=<encryption_key>]
             * : custom encryption/decryption key
             *
             * [--list=<backup_name>]
             * : list backup archive contents
             *
             * @when before_wp_load
             */
            function ($args, $assoc_args) {
                if (\WP_CLI::get_config('quiet')) {
                    $assoc_args['quiet'] = true;
                }
                $this->argv = $args;
                $this->do_cli_execution($assoc_args);
            }
        );
    }

    /**
     * Execute xcloner in CLI mode
     *
     * @param array $opts
     * @return void
     * @throws ArchiveIOException
     * @throws ArchiveIllegalCompressionException
     * @throws Exception
     */
    private function do_cli_execution($opts = array())
    {
        if (!sizeof($opts)) {
            $opts = getopt('v::p:h::q::e:d:k:l:', array('verbose::', 'profile:', 'help::', 'quiet::', 'encrypt:', 'decrypt:', 'key:', 'list:'));
        }

        if (!sizeof($opts)) {
            $opts['h'] = true;
        }

        if (isset($opts['h']) || isset($opts['help'])) {
            echo "-h                        Display help\n";
            echo "-p <profile name>         Specify the backup profile name or ID\n";
            echo "-e <backup name>          Encrypt backup file\n";
            echo "-d <backup name>          Decrypt backup file\n";
            echo "-k <encryption key>       Encryption/Decryption Key\n";
            echo "-l <backup name>          List files inside backup\n";
            echo "-v                        Verbose output\n";
            echo "-q                        Disable output\n";
            return;
        }

        if (isset($opts['q']) || isset($opts['quiet'])) {
            define('XCLONER_DISABLE_OUTPUT', true);
        }

        if (!defined('WP_DEBUG') && !defined('WP_DEBUG_DISPLAY')) {
            define('WP_DEBUG', isset($opts['v']) || isset($opts['verbose']));
            define('WP_DEBUG_DISPLAY', isset($opts['v']) || isset($opts['verbose']));
        }

        $wp_load_path = XCLONER_PLUGIN_DIR . '/../../../wp-load.php';
        if (!file_exists($wp_load_path)) {
            throw new Exception('Can\'t find WordPress load file (wp-load.php)');
        }

        require_once($wp_load_path);

        $profile = [
            'id' => 0
        ];

        $profile_name = "";

        // --profile|p profile name
        if (isset($opts['p']) && $opts['p']) {
            $profile_name = $opts['p'];
        } elseif (isset($opts['profile']) && $opts['profile']) {
            $profile_name = $opts['profile'];
        }

        $xcloner_backup = new Xcloner();

        // --list|l list backup archive
        if (isset($opts['l']) || isset($opts['list'])) {
            $backup_file_path = $opts['list'] . $opts['l'];

            // function to list backup content recursively
            $list_backup_archive_contents = function ($backup_name, $start = 0) use ($xcloner_backup) {
                $xcloner_settings = $xcloner_backup->get_xcloner_settings();
                $xcloner_file_system = $xcloner_backup->get_xcloner_filesystem();

                if ($xcloner_backup->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
                    die(sprintf("%s file is encrypted, please decrypt it first! \n", $backup_name));
                }

                $tar = $xcloner_backup->get_archive_system();

                $backup_parts = [$backup_name];

                if ($xcloner_file_system->is_multipart($backup_name)) {
                    $backup_parts = $xcloner_file_system->get_multipart_files($backup_name);
                }

                foreach ($backup_parts as $backup_name) {
                    if (!$start) {
                        echo sprintf("Processing %s \n", $backup_name);
                    }
                    $tar->open($xcloner_settings->get_xcloner_store_path() . DS . $backup_name, $start);

                    $data = $tar->contents($xcloner_settings->get_xcloner_option('xcloner_files_to_process_per_request'));

                    foreach ($data['extracted_files'] as $key => $file) {
                        echo sprintf("%s (%s) \n", $file->getPath(), size_format($file->getSize()));
                    }

                    if (isset($data['start'])) {
                        call_user_func(__FUNCTION__, $backup_name, $data['start']);
                    }
                }
            };

            $list_backup_archive_contents($backup_file_path);

            exit;
        }

        // --key|k encryption key
        $encryption_key = "";
        if (isset($opts['k']) || isset($opts['key'])) {
            $encryption_key = $opts['key'] . $opts['k'];
        }

        // --encrypt|e encrypt backup archive
        if (isset($opts['e']) || isset($opts['encrypt'])) {
            $backup_name = $opts['encrypt'] . $opts['e'];
            if (!$xcloner_backup->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
                $xcloner_backup->get_xcloner_encryption()->encrypt_file($backup_name, "", $encryption_key, 0, 0, true, true);
            } else {
                die(sprintf('File %s is already encrypted\n', $backup_name));
            }
        }

        // --decrypt|d decrypt backup archive
        if (isset($opts['d']) || isset($opts['decrypt'])) {
            $backup_name = $opts['decrypt'] . $opts['d'];
            if ($xcloner_backup->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
                $xcloner_backup->get_xcloner_encryption()->decrypt_file($backup_name, "", $encryption_key, 0, 0, true);
            } else {
                die(sprintf('File %s is already decrypted\n', $backup_name));
            }
        }

        // start schedule based on profile name
        if (!empty($profile_name)) {
            try {
                $profile = $xcloner_backup->get_xcloner_scheduler()->get_schedule_by_id_or_name($profile_name);
            } catch (Exception $e) {
                die(sprintf('Could not find profile %s', $profile_name));
            }

            $xcloner_backup->execute_backup($profile['id']);
        }
    }

}
