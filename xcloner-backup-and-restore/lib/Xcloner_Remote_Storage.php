<?php

namespace Watchfulli\XClonerCore;

/**
 * XCloner - Backup and Restore backup plugin for Wordpress
 *
 * class-xcloner-remote-storage.php
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
 * @modified 7/25/18 2:15 PM
 *
 */

use Exception;
use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use League\Flysystem\Adapter\Ftp as FtpAdapter;

use League\Flysystem\Sftp\SftpAdapter;

use League\Flysystem\WebDAV\WebDAVAdapter;
use Srmklive\Dropbox\Client\DropboxClient;
use Srmklive\Dropbox\Adapter\DropboxAdapter;

use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

use Mhetreramesh\Flysystem\BackblazeAdapter;
use BackblazeB2\Client as B2Client;

use Microsoft\Graph\Graph;
use As247\Flysystem\OneDrive\OneDriveAdapter;

use GuzzleHttp\Client;

/**
 * Class Xcloner_Remote_Storage
 */
class Xcloner_Remote_Storage
{
    const DEFAULT_GDRIVE_APP_NAME = "XCloner Backup and Restore";
    const DEFAULT_GDRIVE_REDIRECT_URL_WATCHFUL = "https://oauth.xcloner.com/google-drive/";
    const DEFAULT_GDRIVE_CLIENT_ID = "899538417626-5v4t611i8m99315nrslhu061cm1va0e6.apps.googleusercontent.com";
    const DEFAULT_GDRIVE_CLIENT_ID_V2 = "797860151656-8l7of8pkrt87q27q7g3ef0re750ls3er.apps.googleusercontent.com";

    private $storage_fields = array(
        "option_prefix" => "xcloner_",
        "local" => array(
            "text" => "Local",
            "local_enable" => "int",
            "store_path" => "string",
            "start_path" => "string",
            "cleanup_retention_limit_days" => "float",
            "cleanup_exclude_days" => "string",
            "cleanup_retention_limit_archives" => "int",
            "cleanup_capacity_limit" => "int"
        ),
        "ftp" => array(
            "text" => "FTP",
            "ftp_enable" => "int",
            "ftp_hostname" => "string",
            "ftp_port" => "int",
            "ftp_username" => "string",
            "ftp_password" => "raw",
            "ftp_path" => "path",
            "ftp_transfer_mode" => "int",
            "ftp_ssl_mode" => "int",
            "ftp_timeout" => "int",
            "ftp_cleanup_retention_limit_days" => "float",
            "ftp_cleanup_exclude_days" => "string",
            "ftp_cleanup_retention_limit_archives" => "int",
            "ftp_cleanup_capacity_limit" => "int"
        ),
        "sftp" => array(
            "text" => "SFTP",
            "sftp_enable" => "int",
            "sftp_hostname" => "string",
            "sftp_port" => "int",
            "sftp_username" => "string",
            "sftp_password" => "raw",
            "sftp_path" => "path",
            "sftp_private_key" => "raw",
            "sftp_timeout" => "int",
            "sftp_cleanup_retention_limit_days" => "float",
            "sftp_cleanup_exclude_days" => "string",
            "sftp_cleanup_retention_limit_archives" => "int",
            "sftp_cleanup_capacity_limit" => "int"
        ),
        "aws" => array(
            "text" => "S3",
            "aws_enable" => "int",
            "aws_key" => "string",
            "aws_secret" => "raw",
            "aws_endpoint" => "string",
            "aws_region" => "string",
            "aws_bucket_name" => "string",
            "aws_prefix" => "string",
            "aws_cleanup_retention_limit_days" => "float",
            "aws_cleanup_exclude_days" => "string",
            "aws_cleanup_retention_limit_archives" => "int",
            "aws_cleanup_capacity_limit" => "int"
        ),
        "onedrive" => array(
            "text" => "OneDrive",
            "onedrive_enable" => "int",
            "onedrive_client_id" => "string",
            "onedrive_client_secret" => "raw",
            "onedrive_cleanup_retention_limit_days" => "float",
            "onedrive_cleanup_exclude_days" => "string",
            "onedrive_cleanup_retention_limit_archives" => "int",
            "onedrive_cleanup_capacity_limit" => "int",
            "onedrive_path" => "path"
        ),
        "dropbox" => array(
            "text" => "Dropbox",
            "dropbox_enable" => "int",
            "dropbox_access_token" => "string",
            "dropbox_app_secret" => "raw",
            "dropbox_prefix" => "string",
            "dropbox_cleanup_retention_limit_days" => "float",
            "dropbox_cleanup_exclude_days" => "string",
            "dropbox_cleanup_retention_limit_archives" => "int",
            "dropbox_cleanup_capacity_limit" => "int"
        ),
        "azure" => array(
            "text" => "Azure BLOB",
            "azure_enable" => "int",
            "azure_account_name" => "string",
            "azure_api_key" => "raw",
            "azure_container" => "string",
            "azure_cleanup_retention_limit_days" => "float",
            "azure_cleanup_exclude_days" => "string",
            "azure_cleanup_retention_limit_archives" => "int",
            "azure_cleanup_capacity_limit" => "int"
        ),
        "backblaze" => array(
            "text" => "Backblaze",
            "backblaze_enable" => "int",
            "backblaze_account_id" => "string",
            "backblaze_application_key" => "raw",
            "backblaze_bucket_name" => "string",
            "backblaze_bucket_id" => "string",
            "backblaze_cleanup_retention_limit_days" => "float",
            "backblaze_cleanup_exclude_days" => "string",
            "backblaze_cleanup_retention_limit_archives" => "int",
            "backblaze_cleanup_capacity_limit" => "int"
        ),

        "webdav" => array(
            "text" => "WebDAV",
            "webdav_enable" => "int",
            "webdav_url" => "string",
            "webdav_username" => "string",
            "webdav_password" => "raw",
            "webdav_target_folder" => "string",
            "webdav_cleanup_retention_limit_days" => "float",
            "webdav_cleanup_exclude_days" => "string",
            "webdav_cleanup_retention_limit_archives" => "int",
            "webdav_cleanup_capacity_limit" => "int"

        ),

        "gdrive" => array(
            "text" => "Google Drive",
            "gdrive_enable" => "int",
            "gdrive_access_code" => "string",
            "gdrive_target_folder" => "string",
            "gdrive_cleanup_retention_limit_days" => "float",
            "gdrive_empty_trash" => "int",
            "gdrive_cleanup_exclude_days" => "string",
            "gdrive_cleanup_retention_limit_archives" => "int",
            "gdrive_cleanup_capacity_limit" => "int"
        ),
    );

    private $aws_regions = array(
        'us-east-1' => 'US East (N. Virginia)',
        'us-east-2' => 'US East (Ohio)',
        'us-west-1' => 'US West (N. California)',
        'us-west-2' => 'US West (Oregon)',
        'ca-central-1' => 'Canada (Central)',
        'eu-west-1' => 'EU (Ireland)',
        'eu-central-1' => 'EU (Frankfurt)',
        'eu-west-2' => 'EU (London)',
        'eu-west-3' => 'EU (Paris)',
        'eu-south-1' => 'EU (Milan)',
        'eu-north-1' => 'EU (Stockholm)',
        'ap-northeast-1' => 'Asia Pacific (Tokyo)',
        'ap-northeast-2' => 'Asia Pacific (Seoul)',
        'ap-southeast-1' => 'Asia Pacific (Singapore)',
        'ap-southeast-2' => 'Asia Pacific (Sydney)',
        'ap-south-1' => 'Asia Pacific (Mumbai)',
        'sa-east-1' => 'South America (São Paulo)',
        'af-south-1' => 'Africa (Cape Town)',
        'me-south-1' => 'Middle East (Bahrain)'
    );

    private $xcloner_sanitization;
    private $xcloner_file_system;
    private $xcloner_settings;
    private $logger;
    private $xcloner;

    /**
     * Xcloner_Remote_Storage constructor.
     * @param Xcloner $xcloner_container
     */
    public function __construct(Xcloner $xcloner_container)
    {
        $this->xcloner_sanitization = $xcloner_container->get_xcloner_sanitization();
        $this->xcloner_file_system = $xcloner_container->get_xcloner_filesystem();
        $this->xcloner_settings = $xcloner_container->get_xcloner_settings();
        $this->logger = $xcloner_container->get_xcloner_logger()->withName("xcloner_remote_storage");
        $this->xcloner = $xcloner_container;

        foreach ($this->storage_fields as $main_key => $array) {
            if (is_array($array)) {
                foreach ($array as $key => $type) {
                    if ($type == "raw") {
                        add_filter(
                            "pre_update_option_" . $this->storage_fields['option_prefix'] . $key,
                            function ($value) {
                                return $this->simple_crypt($value, 'e');
                            },
                            10,
                            1
                        );

                        add_filter("option_" . $this->storage_fields['option_prefix'] . $key, function ($value) {
                            return $this->simple_crypt($value, 'd');
                        }, 10, 1);
                    }
                }
            }
        }
    }

    /**
     * Encrypts and Decrypt a string based on openssl lib
     *
     * @param $string
     * @param string $action
     * @return string
     */
    private function simple_crypt($string, $action = 'e')
    {
        // you may change these values to your own
        $secret_key = NONCE_KEY;
        $secret_iv = NONCE_SALT;

        if (!$string) {
            //we do no encryption for empty data
            return $string;
        }

        $output = $string;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'e' && function_exists('openssl_encrypt')) {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } else {
            if ($action == 'd' && function_exists('openssl_decrypt') && base64_decode($string)) {
                $decrypt = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
                if ($decrypt) {
                    //we check if decrypt was successful
                    $output = $decrypt;
                }
            }
        }

        return $output;
    }

    private function get_xcloner_container()
    {
        return $this->xcloner;
    }

    public function get_available_storages()
    {
        $return = array();
        foreach ($this->storage_fields as $storage => $data) {
            $check_field = $this->storage_fields["option_prefix"] . $storage . "_enable";
            if ($this->xcloner_settings->get_xcloner_option($check_field)) {
                $return[$storage] = $data['text'];
            }
        }

        return $return;
    }

    /**
     * @throws Exception
     */
    public function save($action = "ftp")
    {
        if (!$action) {
            return false;
        }

        $storage = $this->xcloner_sanitization->sanitize_input_as_string($action);
        $this->logger->debug(sprintf("Saving the storage %s options", strtoupper($action)));

        if (!is_array($this->storage_fields[$storage])) {
            return false;
        }

        foreach ($this->storage_fields[$storage] as $field => $validation) {
            $check_field = $this->storage_fields["option_prefix"] . $field;
            $sanitize_method = "sanitize_input_as_" . $validation;

            //we do not save empty encrypted credentials
            if ($validation == "raw" && str_repeat('*', strlen($_POST[$check_field])) == $_POST[$check_field] && $_POST[$check_field]) {
                continue;
            }

            if (!isset($_POST[$check_field])) {
                $_POST[$check_field] = 0;
            }

            if (!method_exists($this->xcloner_sanitization, $sanitize_method)) {
                $sanitize_method = "sanitize_input_as_string";
            }

            $sanitized_value = $this->xcloner_sanitization->$sanitize_method(stripslashes($_POST[$check_field]));
            update_option($check_field, $sanitized_value);
        }

        $this->xcloner->trigger_message(
            __("%s storage settings saved.", 'xcloner-backup-and-restore'),
            "success",
            $this->storage_fields[$action]['text']
        );

        $this->get_xcloner_container()->check_dependencies();
        return true;
    }

    public function check($action = "ftp")
    {
        try {
            $this->verify_filesystem($action);
            $this->xcloner->trigger_message(
                __("%s connection is valid.", 'xcloner-backup-and-restore'),
                "success",
                $this->storage_fields[$action]['text']
            );
            $this->logger->debug(sprintf("Connection to remote storage %s is valid", strtoupper($action)));
        } catch (Exception $e) {
            $this->xcloner->trigger_message(
                "%s connection error: " . $e->getMessage(),
                "error",
                $this->storage_fields[$action]['text']
            );
        }
    }

    /**
     * @param string $storage_type
     * @throws Exception
     */
    public function verify_filesystem($storage_type)
    {
        $method = "get_" . $storage_type . "_filesystem";

        $this->logger->info(sprintf(
            "Checking validity of the remote storage %s filesystem",
            strtoupper($storage_type)
        ));

        if (!method_exists($this, $method)) {
            return false;
        }

        list($adapter, $filesystem) = $this->$method();

        $test_file = substr(".xcloner_" . md5(time()), 0, 15);

        if ($storage_type == "gdrive") {
            if (!is_array($filesystem->listContents())) {
                throw new Exception(__("Could not read data", 'xcloner-backup-and-restore'));
            }
            $this->logger->debug(sprintf("I can list data from remote storage %s", strtoupper($storage_type)));

            return true;
        }

        //testing write access
        if (!$filesystem->write($test_file, "data")) {
            throw new Exception(__("Could not write data", 'xcloner-backup-and-restore'));
        }
        $this->logger->debug(sprintf("I can write data to remote storage %s", strtoupper($storage_type)));

        //testing read access
        if (!$filesystem->has($test_file)) {
            throw new Exception(__("Could not read data", 'xcloner-backup-and-restore'));
        }
        $this->logger->debug(sprintf("I can read data to remote storage %s", strtoupper($storage_type)));

        //delete test file
        if (!$filesystem->delete($test_file)) {
            throw new Exception(__("Could not delete data", 'xcloner-backup-and-restore'));
        }
        $this->logger->debug(sprintf("I can delete data to remote storage %s", strtoupper($storage_type)));

        return true;
    }

    public function upload_backup_to_storage($file, $storage, $delete_local_copy_after_transfer = 0)
    {
        if (!$this->xcloner_file_system->get_storage_filesystem()->has($file)) {
            $this->logger->print_info(sprintf("File not found %s in local storage", $file));

            return false;
        }

        $method = "get_" . $storage . "_filesystem";

        if (!method_exists($this, $method)) {
            return false;
        }

        list($remote_storage_adapter, $remote_storage_filesystem) = $this->$method();

        //doing remote storage cleaning here
        $this->clean_remote_storage($storage, $remote_storage_filesystem);

        $this->logger->info(
            sprintf("Transferring backup %s to remote storage %s", $file, strtoupper($storage)),
            array("")
        );

        /*if(!$this->xcloner_file_system->get_storage_filesystem()->has($file))
        {
            $this->logger->info(sprintf("File not found %s in local storage", $file));
            return false;
        }*/

        $backup_file_stream = $this->xcloner_file_system->get_storage_filesystem()->readStream($file);

        if (!$remote_storage_filesystem->writeStream($file, $backup_file_stream)) {
            $this->logger->print_info(sprintf("Could not transfer file %s", $file));

            return false;
        }

        if ($this->xcloner_file_system->is_multipart($file)) {
            $parts = $this->xcloner_file_system->get_multipart_files($file);
            if (is_array($parts)) {
                foreach ($parts as $part_file) {
                    $this->logger->print_info(sprintf(
                        "Transferring backup %s to remote storage %s",
                        $part_file,
                        strtoupper($storage)
                    ), array(""));

                    $backup_file_stream = $this->xcloner_file_system->get_storage_filesystem()->readStream($part_file);
                    if (!$remote_storage_filesystem->writeStream($part_file, $backup_file_stream)) {
                        return false;
                    }
                }
            }
        }

        $this->logger->info(sprintf("Upload done, disconnecting from remote storage %s", strtoupper($storage)));

        //CHECK IF WE SHOULD DELETE BACKUP AFTER REMOTE TRANSFER IS DONE
        //if ( $this->xcloner_settings->get_xcloner_option('xcloner_cleanup_delete_after_remote_transfer')) {
        if ($delete_local_copy_after_transfer) {
            $this->logger->print_info(sprintf("Deleting %s from local storage matching rule xcloner_cleanup_delete_after_remote_transfer", $file));
            $this->xcloner_file_system->delete_backup_by_name($file);
        }

        return true;
    }

    public function copy_backup_remote_to_local($file, $storage)
    {
        $method = "get_" . $storage . "_filesystem";

        $target_filename = $file;

        if (!method_exists($this, $method)) {
            return false;
        }

        list($remote_storage_adapter, $remote_storage_filesystem) = $this->$method();

        if (!$remote_storage_filesystem->has($file)) {
            $this->logger->info(sprintf("File not found %s in remote storage %s", $file, strtoupper($storage)));

            return false;
        }

        if ($storage == "gdrive") {
            $metadata = $remote_storage_filesystem->getMetadata($file);
            $target_filename = $metadata['filename'] . "." . $metadata['extension'];
        }

        $this->logger->info(sprintf(
            "Transferring backup %s to local storage from %s storage",
            $file,
            strtoupper($storage)
        ), array(""));

        $backup_file_stream = $remote_storage_filesystem->readStream($file);

        if (!$this->xcloner_file_system->get_storage_filesystem()->writeStream($target_filename, $backup_file_stream)) {
            $this->logger->info(sprintf("Could not transfer file %s", $file));

            return false;
        }

        if ($this->xcloner_file_system->is_multipart($target_filename)) {
            $parts = $this->xcloner_file_system->get_multipart_files($file, $storage);
            if (is_array($parts)) {
                foreach ($parts as $part_file) {
                    $this->logger->info(sprintf(
                        "Transferring backup %s to local storage from %s storage",
                        $part_file,
                        strtoupper($storage)
                    ), array(""));

                    $backup_file_stream = $remote_storage_filesystem->readStream($part_file);
                    if (!$this->xcloner_file_system->get_storage_filesystem()->writeStream(
                        $part_file,
                        $backup_file_stream
                    )) {
                        return false;
                    }
                }
            }
        }

        $this->logger->info(sprintf("Upload done, disconnecting from remote storage %s", strtoupper($storage)));

        return true;
    }

    public function clean_remote_storage($storage, $remote_storage_filesystem)
    {
        return $this->xcloner_file_system->backup_storage_cleanup($storage, $remote_storage_filesystem);
    }

    public function get_local_filesystem()
    {
        $this->get_xcloner_container()->check_dependencies();

        $this->logger->info(sprintf("Creating the Local remote storage connection"), array(""));

        $adapter = new Local($this->xcloner_settings->get_xcloner_option('xcloner_store_path'), LOCK_EX, '0001');

        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        return array($adapter, $filesystem);
    }

    /**
     * @throws Exception
     */
    public function get_azure_filesystem()
    {
        $this->logger->info(sprintf("Creating the AZURE BLOB remote storage connection"), array(""));

        if (version_compare(phpversion(), '7.1.0', '<')) {
            throw new Exception("AZURE BLOB requires PHP 7.1 to be installed!");
        }

        if (!class_exists('XmlWriter')) {
            throw new Exception("AZURE BLOB requires libxml PHP module to be installed with XmlWriter class enabled!");
        }

        $endpoint = sprintf(
            'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
            $this->xcloner_settings->get_xcloner_option("xcloner_azure_account_name"),
            $this->xcloner_settings->get_xcloner_option("xcloner_azure_api_key")
        );

        $blobRestProxy = BlobRestProxy::createBlobService($endpoint);

        $adapter = new AzureBlobStorageAdapter($blobRestProxy, $this->xcloner_settings->get_xcloner_option("xcloner_azure_container"));

        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        return array($adapter, $filesystem);
    }

    public function get_dropbox_filesystem()
    {
        $this->logger->info(sprintf("Creating the DROPBOX remote storage connection"), array(""));

        $client = new DropboxClient($this->xcloner_settings->get_xcloner_option("xcloner_dropbox_access_token"));
        $adapter = new DropboxAdapter($client, $this->xcloner_settings->get_xcloner_option("xcloner_dropbox_prefix"));

        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        return array($adapter, $filesystem);
    }

    /**
     * @throws Exception
     */
    public function get_aws_filesystem()
    {
        $this->logger->info(sprintf("Creating the S3 remote storage connection"), array(""));

        if (!class_exists('XmlWriter')) {
            throw new Exception("AZURE BLOB requires libxml PHP module to be installed with XmlWriter class enabled!");
        }


        $credentials = array(
            'credentials' => array(
                'key' => $this->xcloner_settings->get_xcloner_option("xcloner_aws_key"),
                'secret' => $this->xcloner_settings->get_xcloner_option("xcloner_aws_secret")
            ),
            'region' => $this->xcloner_settings->get_xcloner_option("xcloner_aws_region"),
            'version' => 'latest',
        );

        if ($this->xcloner_settings->get_xcloner_option('xcloner_aws_endpoint')
            /*&& !$this->xcloner_settings->get_xcloner_option("xcloner_aws_region")*/) {
            $credentials['endpoint'] = $this->xcloner_settings->get_xcloner_option('xcloner_aws_endpoint');
        }
        $client = @new S3Client($credentials);

        $adapter = new AwsS3Adapter($client, $this->xcloner_settings->get_xcloner_option("xcloner_aws_bucket_name"), $this->xcloner_settings->get_xcloner_option("xcloner_aws_prefix"));
        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        return array($adapter, $filesystem);
    }

    /**
     * @throws Exception
     */
    public function get_backblaze_filesystem()
    {
        $this->logger->info(sprintf("Creating the BACKBLAZE remote storage connection"), array(""));

        $client = new B2Client(
            $this->xcloner_settings->get_xcloner_option("xcloner_backblaze_account_id"),
            $this->xcloner_settings->get_xcloner_option("xcloner_backblaze_application_key")
        );

        $bucket_id = $this->xcloner_settings->get_xcloner_option("xcloner_backblaze_bucket_id");

        $adapter = new BackblazeAdapter(
            $client,
            $this->xcloner_settings->get_xcloner_option("xcloner_backblaze_bucket_name"),
            empty($bucket_id) ? null : $bucket_id,
        );

        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        return array($adapter, $filesystem);
    }

    public function get_webdav_filesystem()
    {
        $this->logger->info(sprintf("Creating the WEBDAV remote storage connection"), array(""));

        $settings = array(
            'baseUri' => $this->xcloner_settings->get_xcloner_option("xcloner_webdav_url"),
            'userName' => $this->xcloner_settings->get_xcloner_option("xcloner_webdav_username"),
            'password' => $this->xcloner_settings->get_xcloner_option("xcloner_webdav_password"),
            'authType' => \Sabre\DAV\Client::AUTH_BASIC,
            //'proxy' => 'locahost:8888',
        );

        $client = new \Sabre\DAV\Client($settings);
        $adapter = new WebDAVAdapter($client, $this->xcloner_settings->get_xcloner_option("xcloner_webdav_target_folder"));
        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        return array($adapter, $filesystem);
    }

    /**
     * OneDrive connector
     *
     * https://docs.microsoft.com/en-us/onedrive/developer/rest-api/getting-started/graph-oauth?view=odsp-graph-online#token-flow
     */
    public function get_onedrive_filesystem()
    {
        $accessToken = get_option('xcloner_onedrive_access_token');

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $adapter = new OneDriveAdapter($graph, '[root path]');
        $adapter->setPathPrefix('/drive/root:/' . urldecode(get_option('xcloner_onedrive_path') ?: '') . "/");
        $filesystem = new Filesystem($adapter);

        return [$adapter, $filesystem];
    }

    public function gdrive_construct()
    {
        if (!class_exists('Google_Client')) {
            return false;
        }

        $client = new \Google_Client();
        $client->setApplicationName(self::DEFAULT_GDRIVE_APP_NAME);
        $client->setClientId($this->gdrive_get_client_id());

        $client->setRedirectUri($this->gdrive_get_redirect_url());
        $client->addScope('https://www.googleapis.com/auth/drive');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        return $client;
    }

    private function gdrive_get_client_id()
    {
        if (!empty(getenv('XCLONER_GDRIVE_CLIENT_ID'))) {
            return getenv('XCLONER_GDRIVE_CLIENT_ID');
        }

        return self::DEFAULT_GDRIVE_CLIENT_ID;
    }

    private function gdrive_get_redirect_url()
    {
        if (!empty(getenv('XCLONER_GDRIVE_REDIRECT_URL'))) {
            return getenv('XCLONER_GDRIVE_REDIRECT_URL');
        }

        return self::DEFAULT_GDRIVE_REDIRECT_URL_WATCHFUL;
    }

    public function gdrive_get_auth_url()
    {
        $client = $this->gdrive_construct();

        if (!$client) {
            return false;
        }

        return $client->createAuthUrl();
    }

    public function gdrive_set_access_token($code)
    {
        $client = $this->gdrive_construct();

        if (!$client) {
            $error_msg = "Could not initialize the Google Drive Class, please check that the xcloner-google-drive plugin is enabled...";
            $this->logger->error($error_msg);

            return false;
        }

        $client->setApplicationName(self::DEFAULT_GDRIVE_APP_NAME);

        return $this->gdrive_fetch_token_with_watchful_configuration([
            'gdrive_auth_code' => $code
        ]);
    }


    private function gdrive_fetch_token_with_watchful_configuration(array $form_params)
    {
        $client = new Client([
            'timeout' => 2.0,
        ]);

        $form_params = array_merge($form_params, [
            'use_v2' => 1
        ]);

        $response = $client->request(
            'POST',
            $this->gdrive_get_redirect_url(),
            [
                'form_params' => $form_params
            ]
        );

        $token = json_decode($response->getBody()->getContents(), true);

        if (empty($token['access_token'])) {
            $this->xcloner->trigger_message(
                "%s connection error: Failed to get the AUTH code (" . $token['error'] . ")"
            );
            return $token;
        }

        update_option("xcloner_gdrive_access_token", $token['access_token']);

        if (!empty($token['refresh_token'])) {
            update_option("xcloner_gdrive_refresh_token", $token['refresh_token']);
        }

        return $token;
    }

    /**
     * @throws Exception
     */
    public function get_gdrive_filesystem()
    {
        if (version_compare(phpversion(), '7.1.0', '<')) {
            throw new Exception("Google Drive API requires PHP 7.1 to be installed!");
        }

        $this->logger->info(sprintf("Creating the Google Drive remote storage connection"), array(""));

        $client = $this->gdrive_construct();

        if (!$client) {
            $error_msg = "Could not initialize the Google Drive Class, please check that the xcloner-google-drive plugin is enabled...";
            $this->logger->error($error_msg);
            throw new Exception($error_msg);
        }

        $access_token = $this->xcloner_settings->get_xcloner_option("xcloner_gdrive_access_token");
        $client->setAccessToken($access_token);

        if ($client->isAccessTokenExpired()) {
            $access_token = $this->gdrive_fetch_token_with_watchful_configuration([
                'gdrive_auth_refresh' => $this->xcloner_settings->get_xcloner_option("xcloner_gdrive_refresh_token")
            ]);

            if ($access_token) {
                $client->setAccessToken($access_token);
            }
        }

        $service = new \Google_Service_Drive($client);

        if ($this->xcloner_settings->get_xcloner_option("xcloner_gdrive_empty_trash", 0)) {
            $this->logger->info(sprintf("Doing a Google Drive emptyTrash call"), array(""));
            $service->files->emptyTrash();
        }

        $parent = 'root';
        $dir = basename($this->xcloner_settings->get_xcloner_option("xcloner_gdrive_target_folder"));

        $folderID = $this->xcloner_settings->get_xcloner_option("xcloner_gdrive_target_folder");

        $tmp = parse_url($folderID);

        if (isset($tmp['query'])) {
            $folderID = str_replace("id=", "", $tmp['query']);
        }

        if (stristr($folderID, "/")) {
            $query = sprintf(
                'mimeType = \'application/vnd.google-apps.folder\' and \'%s\' in parents and name contains \'%s\'',
                $parent,
                $dir
            );
            $response = $service->files->listFiles([
                'pageSize' => 1,
                'q' => $query
            ]);

            if (sizeof($response)) {
                foreach ($response as $obj) {
                    $folderID = $obj->getId();
                }
            } else {
                $this->xcloner->trigger_message(sprintf(__(
                    "Could not find folder ID by name %s",
                    'xcloner-backup-and-restore'
                ), $folderID), "error");
            }
        }

        $this->logger->info(sprintf("Using target folder with ID %s on the remote storage", $folderID));

        $adapter = new GoogleDriveAdapter($service, $folderID);

        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));


        return array($adapter, $filesystem);
    }

    public function get_ftp_filesystem()
    {
        $this->logger->info('Creating the FTP remote storage connection');

        $ftp_path = urldecode($this->xcloner_settings->get_xcloner_option("xcloner_ftp_path") ?: null);

        $adapter = new FtpAdapter([
            'host' => $this->xcloner_settings->get_xcloner_option("xcloner_ftp_hostname"),
            'username' => $this->xcloner_settings->get_xcloner_option("xcloner_ftp_username"),
            'password' => $this->xcloner_settings->get_xcloner_option("xcloner_ftp_password"),

            /** optional config settings */
            'port' => $this->xcloner_settings->get_xcloner_option("xcloner_ftp_port", 21),
            'root' => $ftp_path,
            'passive' => $this->xcloner_settings->get_xcloner_option("xcloner_ftp_transfer_mode"),
            'ssl' => $this->xcloner_settings->get_xcloner_option("xcloner_ftp_ssl_mode"),
            'timeout' => $this->xcloner_settings->get_xcloner_option("xcloner_ftp_timeout", 30),
        ]);

        $adapter->connect();

        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        return array($adapter, $filesystem);
    }

    public function get_sftp_filesystem()
    {
        $this->logger->info('Creating the SFTP remote storage connection');

        $sftp_path = urldecode($this->xcloner_settings->get_xcloner_option("xcloner_sftp_path") ?: './');

        $adapter = new SftpAdapter([
            'host' => $this->xcloner_settings->get_xcloner_option("xcloner_sftp_hostname"),
            'username' => $this->xcloner_settings->get_xcloner_option("xcloner_sftp_username"),
            'password' => $this->xcloner_settings->get_xcloner_option("xcloner_sftp_password"),

            /** optional config settings */
            'port' => $this->xcloner_settings->get_xcloner_option("xcloner_sftp_port", 22),
            'root' => $sftp_path,
            'privateKey' => $this->xcloner_settings->get_xcloner_option("xcloner_sftp_private_key"),
            'timeout' => $this->xcloner_settings->get_xcloner_option("xcloner_sftp_timeout", 30),
            'directoryPerm' => 0755
        ]);

        $filesystem = new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));

        return array($adapter, $filesystem);
    }

    public function change_storage_status($field, $value)
    {
        $field = $this->xcloner_sanitization->sanitize_input_as_string($field);
        $value = $this->xcloner_sanitization->sanitize_input_as_int($value);

        return update_option($field, $value);
    }

    public function get_aws_regions()
    {
        return $this->aws_regions;
    }
}
