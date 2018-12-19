<?php
/**
 * Created by PhpStorm.
 * User: thinkovi
 * Date: 2018-11-27
 * Time: 12:11
 */

//namespace XCloner;

class Xcloner_Encryption
{
    const FILE_ENCRYPTION_BLOCKS = 1024*1024;
    const FILE_ENCRYPTION_SUFFIX = ".encrypted";
    const FILE_DECRYPTION_SUFFIX = ".decrypted";

    private $xcloner_settings;
    private $logger;
    private $verification = false;

    public function __construct(Xcloner $xcloner_container)
    {
        $this->xcloner_container = $xcloner_container;
        $this->xcloner_settings  = $xcloner_container->get_xcloner_settings();
        $this->logger            = $xcloner_container->get_xcloner_logger()->withName( "xcloner_encryption" );
    }

    /**
     * Returns the backup encryption key
     *
     * @return SECURE_AUTH_SALT|null
     */
    public function get_backup_encryption_key()
    {
        return $this->xcloner_settings->get_xcloner_encryption_key();

    }

    /**
     * Check if provided filename has encrypted suffix
     *
     * @param $filename
     * @return bool
     */
    public function is_encrypted_file($filename) {
        if(stristr( $filename, self::FILE_ENCRYPTION_SUFFIX)) {

            $fp = fopen($this->xcloner_settings->get_xcloner_store_path() . DS .$filename, 'r');
            $encryption_length = fread($fp, 16);
            fclose($fp);
            if(is_numeric($encryption_length)) {
                return true;
            }

            //return true;
        }

        return false;

    }

    /**
     * Returns the filename with encrypted suffix
     *
     * @param $filename
     * @return string
     */
    public function get_encrypted_target_backup_file_name( $filename ) {

        return str_replace(self::FILE_DECRYPTION_SUFFIX, "", $filename) . self::FILE_ENCRYPTION_SUFFIX;
    }

    /**
     * Returns the filename without encrypted suffix
     *
     * @param $filename
     * @return string
     */
    public function get_decrypted_target_backup_file_name( $filename ) {

        return str_replace(self::FILE_ENCRYPTION_SUFFIX, self::FILE_DECRYPTION_SUFFIX, $filename);
    }

    /**
     * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
     *
     * @param string $source Path to file that should be encrypted
     * @param string $dest   File name where the encryped file should be written to.
     * @param string $key    The key used for the encryption
     * @param int $start   Start position for reading when doing incremental mode.
     * @param string $iv   The IV key to use.
     * @param bool $verfication   Weather we should we try to verify the decryption.
     * @return string|false  Returns the file name that has been created or FALSE if an error occured
    */
    public function encrypt_file($source, $dest = "" , $key= "", $start = 0, $iv = 0, $verification = false)
    {
        //$key = substr(sha1($key, true), 0, 16);
        if(!$key){
            $key = self::get_backup_encryption_key();
        }
        $key_digest = openssl_digest ($key, "md5", true);

        if(!$dest) {
            $dest = $this->get_encrypted_target_backup_file_name($source);
        }

        if(!$iv) {
            //$iv = openssl_random_pseudo_bytes(16);
            $iv = str_pad(self::FILE_ENCRYPTION_BLOCKS, 16, "0000000000000000", STR_PAD_LEFT);
        }

        if( !$start ) {
            $fpOut = fopen($this->xcloner_settings->get_xcloner_store_path() . DS .$dest, 'w');
        }else{
            $fpOut = fopen($this->xcloner_settings->get_xcloner_store_path() . DS .$dest, 'a');
        }

        if ( $fpOut ) {

            // Put the initialization vector to the beginning of the file
            if(!$start) {
                fwrite($fpOut, $iv);
            }

            if ($fpIn = fopen($this->xcloner_settings->get_xcloner_store_path() . DS .$source, 'rb')) {

                fseek($fpIn, $start);

                if (!feof($fpIn)) {

                    $plaintext = fread($fpIn, 16 * self::FILE_ENCRYPTION_BLOCKS);
                    $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key_digest, OPENSSL_RAW_DATA, $iv);

                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    //$iv = openssl_random_pseudo_bytes(16);

                    fwrite($fpOut, $ciphertext);

                    $start = ftell($fpIn);

                    fclose($fpOut );

                    if(!feof($fpIn)) {
                        fclose($fpIn);
                        //echo "\n NEW:".$key.md5($iv);
                        //self::encryptFile($source, $dest, $key, $start, $iv);
                        return array(
                            "start" => $start,
                            "iv" => urlencode($iv),
                            "target_file" => $dest,
                            "finished" => 0
                        );
                    }

                }
            } else {
                $this->logger->error('Unable to read source file for encryption.');
                throw new \Exception("Unable to read source file for encryption.");
            }
        } else {
            $this->logger->error('Unable to write destination file for encryption.');
            throw new \Exception("Unable to write destination file for encryption.");
        }

        $this->verify_encrypted_file($dest);

        return array("target_file" => $dest, "finished" => 1);
    }

    public function verify_encrypted_file($file) {
        $this->verification = true;
        $this->decrypt_file($file);
        $this->verification = false;
    }

    /**
     * Dencrypt the passed file and saves the result in a new file, removing the
     * last 4 characters from file name.
     *
     * @param string $source Path to file that should be decrypted
     * @param string $dest   File name where the decryped file should be written to.
     * @param string $key    The key used for the decryption (must be the same as for encryption)
     * @param int $start   Start position for reading when doing incremental mode.
     * @param string $iv   The IV key to use.
     * @return string|false  Returns the file name that has been created or FALSE if an error occured
     */
    public function decrypt_file($source, $dest = "", $key = "", $start = 0, $iv = 0)
    {
        //$key = substr(sha1($key, true), 0, 16);
        if(!$key){
            $key = self::get_backup_encryption_key();
        }

        $key_digest = openssl_digest ($key, "md5", true);

        if(!$dest) {
            $dest = $this->get_decrypted_target_backup_file_name($source);
        }

        if( !$start ) {
            if($this->verification){
                $fpOut = fopen("php://stdout", 'w');
            }else {
                $fpOut = fopen($this->xcloner_settings->get_xcloner_store_path() . DS . $dest, 'w');
            }
        }else{
            if($this->verification){
                $fpOut = fopen("php://stdout", 'a');
            }else {
                $fpOut = fopen($this->xcloner_settings->get_xcloner_store_path() . DS . $dest, 'a');
            }
        }

        if ( $fpOut ) {
            if ($fpIn = fopen($this->xcloner_settings->get_xcloner_store_path() . DS .$source, 'rb')) {

                $encryption_length = (int)fread($fpIn, 16);
                if(!$encryption_length) {
                    $encryption_length = self::FILE_ENCRYPTION_BLOCKS;
                }

                fseek($fpIn, $start);

                // Get the initialzation vector from the beginning of the file
                if(!$iv) {
                    $iv = fread($fpIn, 16);
                }

                if (!feof($fpIn)) {

                    // we have to read one block more for decrypting than for encrypting
                    $ciphertext = fread($fpIn, 16 * ($encryption_length + 1));
                    $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key_digest, OPENSSL_RAW_DATA, $iv);

                    if(!$plaintext){
                        unlink($this->xcloner_settings->get_xcloner_store_path() . DS . $dest);
                        $this->logger->error('Backup decryption failed, please check your provided Encryption Key.');
                        throw new \Exception("Backup decryption failed, please check your provided Encryption Key.");
                    }

                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);

                    if( !$this->verification) {
                        fwrite($fpOut, $plaintext);
                    }

                    $start = ftell($fpIn);

                    fclose($fpOut );

                    if(!feof($fpIn)) {
                        fclose($fpIn);
                        if($this->verification) {
                            $ciphertext = "";
                            $plaintext = "";
                            $this->decrypt_file($source, $dest, $key, $start, $iv);
                        }else {
                            return array(
                                "start" => $start,
                                "iv" => urlencode($iv),
                                "target_file" => $dest,
                                "finished" => 0
                            );
                        }
                    }

                }
            } else {
                $this->logger->error('Unable to read source file for decryption');
                throw new \Exception("Unable to read source file for decryption");
            }
        } else {
            $this->logger->error('Unable to write destination file for decryption');
            throw new \Exception("Unable to write destination file for decryption");
        }

        return array("target_file" => $dest, "finished" => 1);
    }

}

/*
try {
    if (isset($argv[1]) && $argv[1] == "-e") {
        Xcloner_Encryption::encrypt_file($argv[2], $argv[2] . ".enc");
    } elseif (isset($argv[1]) && $argv[1] == "-d") {
        Xcloner_Encryption::decrypt_file($argv[2], $argv[2] . ".out");
    }
}catch(\Exception $e) {
    echo "CAUGHT: " . $e->getMessage();
}
*/