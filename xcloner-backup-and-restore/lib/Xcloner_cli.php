<?php

namespace Watchfulli\XClonerCore;

use Exception;
use League\Flysystem\FileNotFoundException;
use splitbrain\PHPArchive\ArchiveCorruptedException;
use splitbrain\PHPArchive\ArchiveIllegalCompressionException;
use splitbrain\PHPArchive\ArchiveIOException;
use splitbrain\PHPArchive\FileInfoException;

class Xcloner_cli
{
    private $argv;

    /** @var Xcloner */
    private $xcloner_container;

    public function __construct($argv) {
        $this->argv = $argv;
    }

    public function should_run()
    {
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
        if (!$this->should_run()) {
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

        if ($this->should_print_help($opts)) {
            $this->print_help();
            exit;
        }

        if ($this->should_be_quiet($opts)) {
            define('XCLONER_DISABLE_OUTPUT', true);
        }

        $this->load_wordpress();

        if (
            !defined('WP_DEBUG') && !defined('WP_DEBUG_DISPLAY')
        ) {
            define('WP_DEBUG', $this->should_be_verbose($opts));
            define('WP_DEBUG_DISPLAY', $this->should_be_verbose($opts));
        }

        $this->xcloner_container = new Xcloner();

        // --list|l list backup archive
        if ($this->should_list_backup_contents($opts)) {
            $this->list_backup_contents($opts);
            exit;
        }

        // --encrypt|e encrypt backup archive
        if ($this->should_encrypt_backup($opts)) {
            $this->encrypt_backup($opts);
            exit;
        }

        // --decrypt|d decrypt backup archive
        if ($this->should_decrypt_backup($opts)) {
            $this->decrypt_backup($opts);
            exit;
        }

        // --profile|p backup profile name or id
        if ($this->should_execute_backup($opts)) {
            $this->execute_backup($opts);
            exit;
        }

        $this->print_help();
    }

    private function should_print_help($opts)
    {
        return
            isset($opts['h']) ||
            isset($opts['help']) ||
            !sizeof($opts);
    }

    private function print_help()
    {
        echo "-h                        Display help" . PHP_EOL;
        echo "-p <profile name>         Specify the backup profile name or ID" . PHP_EOL;
        echo "-e <backup name>          Encrypt backup file" . PHP_EOL;
        echo "-d <backup name>          Decrypt backup file" . PHP_EOL;
        echo "-k <encryption key>       Encryption/Decryption Key" . PHP_EOL;
        echo "-l <backup name>          List files inside backup" . PHP_EOL;
        echo "-v                        Verbose output" . PHP_EOL;
        echo "-q                        Disable output" . PHP_EOL;
    }

    /**
     * @throws Exception
     */
    private function load_wordpress()
    {
        $wp_load_path = XCLONER_PLUGIN_DIR . '/../../../wp-load.php';
        if (!file_exists($wp_load_path)) {
            throw new Exception('Can\'t find WordPress load file (wp-load.php)');
        }

        require_once($wp_load_path);
    }

    private function should_list_backup_contents($opts)
    {
        return isset($opts['l']) || isset($opts['list']);
    }

    private function get_backup_name_to_list($opts)
    {
        if (isset($opts['l'])) {
            return $opts['l'];
        }

        if (isset($opts['list'])) {
            return $opts['list'];
        }

        return '';
    }


    /**
     * @throws FileNotFoundException
     * @throws ArchiveIllegalCompressionException
     * @throws ArchiveIOException
     * @throws Exception
     */
    private function list_backup_contents($opts)
    {
        $backup_name = $this->get_backup_name_to_list($opts);

        $xcloner_settings = $this->xcloner_container->get_xcloner_settings();
        $xcloner_file_system = $this->xcloner_container->get_xcloner_filesystem();

        if ($this->xcloner_container->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
            die(sprintf("%s file is encrypted, please decrypt it first! " . PHP_EOL, $backup_name));
        }

        $tar = $this->xcloner_container->get_archive_system();

        $backup_parts = [$backup_name];

        if ($xcloner_file_system->is_multipart($backup_name)) {
            $backup_parts = $xcloner_file_system->get_multipart_files($backup_name);
        }

        foreach ($backup_parts as $backup_name) {
            $tar->open($xcloner_settings->get_xcloner_store_path() . DS . $backup_name);

            $data = $tar->contents($xcloner_settings->get_xcloner_option('xcloner_files_to_process_per_request'));

            foreach ($data['extracted_files'] as $file) {
                echo sprintf("%s (%s) " . PHP_EOL, $file->getPath(), size_format($file->getSize()));
            }
        }
    }

    private function get_encryption_key_from_arguments($opts)
    {
        if (isset($opts['k'])) {
            return $opts['k'];
        }

        if (isset($opts['key'])) {
            return $opts['key'];
        }

        return null;
    }

    private function should_encrypt_backup($opts)
    {
        return isset($opts['e']) || isset($opts['encrypt']);
    }

    private function get_backup_name_to_encrypt($opts)
    {
        if (isset($opts['e'])) {
            return $opts['e'];
        }

        if (isset($opts['encrypt'])) {
            return $opts['encrypt'];
        }

        return '';
    }

    private function encrypt_backup($opts)
    {
        $backup_name = $this->get_backup_name_to_encrypt($opts);
        if ($this->xcloner_container->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
            die(sprintf('File %s is already encrypted'. PHP_EOL, $backup_name));
        }
        $this->xcloner_container->get_xcloner_encryption()->encrypt_file($backup_name, "", $this->get_encryption_key_from_arguments($opts), 0, 0, true, true);
        echo sprintf('File %s encrypted successfully'. PHP_EOL, $backup_name);
    }

    private function should_decrypt_backup($opts)
    {
        return isset($opts['d']) || isset($opts['decrypt']);
    }

    private function get_backup_name_to_decrypt($opts)
    {
        if (isset($opts['d'])) {
            return $opts['d'];
        }

        if (isset($opts['decrypt'])) {
            return $opts['decrypt'];
        }

        return '';
    }

    private function decrypt_backup($opts)
    {
        $backup_name = $this->get_backup_name_to_decrypt($opts);
        if (!$this->xcloner_container->get_xcloner_encryption()->is_encrypted_file($backup_name)) {
            die(sprintf('File %s is already decrypted' . PHP_EOL, $backup_name));
        }

        $this->xcloner_container->get_xcloner_encryption()->decrypt_file($backup_name, "", $this->get_encryption_key_from_arguments($opts), 0, 0, true);
        echo sprintf('File %s decrypted successfully' . PHP_EOL, $backup_name);
    }

    private function should_be_verbose($opts)
    {
        return (isset($opts['v']) || isset($opts['verbose'])) && !$this->should_be_quiet($opts);
    }

    private function should_be_quiet($opts)
    {
        return isset($opts['q']) || isset($opts['quiet']);
    }

    private function should_execute_backup($opts)
    {
        return isset($opts['p']) || isset($opts['profile']);
    }

    private function get_profile_name_from_arguments($opts)
    {
        if (isset($opts['p'])) {
            return $opts['p'];
        }

        if (isset($opts['profile'])) {
            return $opts['profile'];
        }

        return null;
    }

    /**
     * @throws ArchiveCorruptedException
     * @throws ArchiveIOException
     * @throws FileInfoException
     * @throws ArchiveIllegalCompressionException
     */
    private function execute_backup($opts)
    {
        $profile_name = $this->get_profile_name_from_arguments($opts);
        try {
            $profile = $this->xcloner_container->get_xcloner_scheduler()->get_schedule_by_id_or_name($profile_name);
        } catch (Exception $e) {
            die(sprintf('Could not find profile %s', $profile_name));
        }

        $this->xcloner_container->get_xcloner_scheduler()->xcloner_scheduler_callback($profile['id']);
    }

}
