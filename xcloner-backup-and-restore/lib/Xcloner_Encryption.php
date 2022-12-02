<?php

namespace Watchfulli\XClonerCore;

class Xcloner_Encryption
{
    const FILE_ENCRYPTION_BLOCKS = 1024 * 1024;
    const FILE_ENCRYPTION_SUFFIX = ".encrypted";
    const FILE_DECRYPTION_SUFFIX = ".decrypted";

    private $xcloner_settings;
    private $logger;
    private $xcloner_container;
    private $verification = false;

    /**
     * Xcloner_Encryption constructor.
     * @param Xcloner $xcloner_container
     */
    public function __construct(Xcloner $xcloner_container)
    {
        $this->xcloner_container = $xcloner_container;
        $this->xcloner_settings = $xcloner_container->get_xcloner_settings();

        if (property_exists($xcloner_container, 'xcloner_logger')) {
            $this->logger = $xcloner_container->get_xcloner_logger()->withName("xcloner_encryption");
        } else {
            $this->logger = "";
        }
    }

    /**
     * Returns the backup encryption key
     *
     * @return string|null
     */
    public function get_backup_encryption_key()
    {
        if (is_object($this->xcloner_settings)) {
            return $this->xcloner_settings->get_xcloner_encryption_key();
        }

        return null;
    }

    /**
     * Check if provided filename has encrypted suffix
     *
     * @param $filename
     * @return bool
     */
    public function is_encrypted_file($filename)
    {
        $fp = fopen($this->get_xcloner_path() . $filename, 'r');
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
     * Returns the filename with encrypted suffix
     *
     * @param string $filename
     * @return string
     */
    public function get_encrypted_target_backup_file_name($filename)
    {
        return str_replace(self::FILE_DECRYPTION_SUFFIX, "", $filename) . self::FILE_ENCRYPTION_SUFFIX;
    }

    /**
     * Returns the filename without encrypted suffix
     *
     * @param string $filename
     * @return string
     */
    public function get_decrypted_target_backup_file_name($filename)
    {
        return str_replace(self::FILE_ENCRYPTION_SUFFIX, "", $filename) . self::FILE_DECRYPTION_SUFFIX;
    }

    /**
     * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
     *
     * @param string $source Path to file that should be encrypted
     * @param string $dest File name where the encryped file should be written to.
     * @param string $key The key used for the encryption
     * @param int $start Start position for reading when doing incremental mode.
     * @param string $iv The IV key to use.
     * @param bool $verification Weather we should we try to verify the decryption.
     * @return array|false  Returns array or FALSE if an error occured
     * @throws Exception
     */
    public function encrypt_file($source, $dest = "", $key = "", $start = 0, $iv = 0, $verification = true, $recursive = false)
    {
        if (is_object($this->logger)) {
            if ($recursive && !$start) {
                $this->logger->print_info(sprintf('Encrypting file %s at position %d IV %s', $source, $start, base64_encode($iv)));
            } else {
                $this->logger->info(sprintf('Encrypting file %s at position %d IV %s', $source, $start, base64_encode($iv)));
            }
        }

        //$key = substr(sha1($key, true), 0, 16);
        if (!$key) {
            $key = $this->get_backup_encryption_key();
        }
        $key_digest = openssl_digest($key, "md5", true);

        $keep_local = 1;
        if (!$dest || $dest == $this->get_encrypted_target_backup_file_name($source)) {
            $dest = $this->get_encrypted_target_backup_file_name($source);
            $keep_local = 0;
        }

        if (!$iv || !$start) {
            //$iv = openssl_random_pseudo_bytes(16);
            $iv = str_pad(self::FILE_ENCRYPTION_BLOCKS, 16, "0000000000000000", STR_PAD_LEFT);
        }

        if (!$start) {
            $fpOut = fopen($this->get_xcloner_path() . $dest, 'w');
        } else {
            $fpOut = fopen($this->get_xcloner_path() . $dest, 'a');
        }

        if (is_resource($fpOut)) {

            // Put the initialization vector to the beginning of the file
            if (!$start) {
                fwrite($fpOut, $iv);
            }

            if (file_exists($this->get_xcloner_path() . $source) &&
                $fpIn = fopen($this->get_xcloner_path() . $source, 'rb')) {
                fseek($fpIn, (int)$start);

                if (!feof($fpIn)) {
                    $plaintext = fread($fpIn, 16 * self::FILE_ENCRYPTION_BLOCKS);
                    $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key_digest, OPENSSL_RAW_DATA, $iv);

                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    //$iv = openssl_random_pseudo_bytes(16);

                    fwrite($fpOut, $ciphertext);

                    $start = ftell($fpIn);

                    fclose($fpOut);

                    unset($ciphertext);
                    unset($plaintext);

                    if (!feof($fpIn)) {
                        fclose($fpIn);
                        //echo "\n NEW:".$key.md5($iv);
                        //self::encryptFile($source, $dest, $key, $start, $iv);
                        if ($recursive) {
                            return $this->encrypt_file($source, $dest, $key, $start, ($iv), $verification, $recursive);
                        } else {
                            if (($iv) != base64_decode(base64_encode($iv))) {
                                throw new \Exception('Could not encode IV for transport');
                            }

                            return array(
                                "start" => $start,
                                "iv" => base64_encode($iv),
                                "target_file" => $dest,
                                "finished" => 0
                            );
                        }
                    }
                }
            } else {
                if (is_object($this->logger)) {
                    $this->logger->error('Unable to read source file for encryption.');
                }
                throw new \Exception("Unable to read source file for encryption.");
            }
        } else {
            if (is_object($this->logger)) {
                $this->logger->error('Unable to write destination file for encryption.');
            }
            throw new \Exception("Unable to write destination file for encryption.");
        }

        if ($verification) {
            $this->verify_encrypted_file($dest, $key);
        }

        //we replace the original backup with the encrypted one
        if (!$keep_local && copy(
                $this->get_xcloner_path() . $dest,
                $this->get_xcloner_path() . $source
            )) {
            unlink($this->get_xcloner_path() . $dest);
        }


        return array("target_file" => $dest, "finished" => 1);
    }

    /**
     * @param string $file
     */
    public function verify_encrypted_file($file, $key = "")
    {
        if (is_object($this->logger)) {
            $this->logger->print_info(sprintf('Verifying encrypted file %s', $file));
        }

        $this->verification = true;
        $this->decrypt_file($file, $file, $key);
        $this->verification = false;
    }

    /**
     * Dencrypt the passed file and saves the result in a new file, removing the
     * last 4 characters from file name.
     *
     * @param string $source Path to file that should be decrypted
     * @param string $dest File name where the decryped file should be written to.
     * @param string $key The key used for the decryption (must be the same as for encryption)
     * @param int $start Start position for reading when doing incremental mode.
     * @param string $iv The IV key to use.
     * @return array|false  Returns array or FALSE if an error occured
     * @throws Exception
     */
    public function decrypt_file($source, $dest = "", $key = "", $start = 0, $iv = 0, $recursive = false)
    {
        if (is_object($this->logger)) {
            if ($recursive && !$start) {
                $this->logger->print_info(sprintf('Decrypting file %s at position %d with IV %s', $source, $start, base64_encode($iv)));
            } else {
                $this->logger->info(sprintf('Decrypting file %s at position %d with IV %s', $source, $start, base64_encode($iv)));
            }
        }

        //$key = substr(sha1($key, true), 0, 16);
        if (!$key) {
            $key = $this->get_backup_encryption_key();
        }

        $key_digest = openssl_digest($key, "md5", true);

        $keep_local = 1;
        if (!$dest) {
            $dest = $this->get_decrypted_target_backup_file_name($source);
            $keep_local = 0;
        }

        if (!$start) {
            if ($this->verification) {
                $fpOut = fopen("php://stdout", 'w');
            } else {
                $fpOut = fopen($this->get_xcloner_path() . $dest, 'w');
            }
        } else {
            if ($this->verification) {
                $fpOut = fopen("php://stdout", 'a');
            } else {
                $fpOut = fopen($this->get_xcloner_path() . $dest, 'a');
            }
        }

        if (is_resource($fpOut)) {
            if (file_exists($this->get_xcloner_path() . $source) &&
                $fpIn = fopen($this->get_xcloner_path() . $source, 'rb')) {
                $encryption_length = (int)fread($fpIn, 16);
                if (!$encryption_length) {
                    $encryption_length = self::FILE_ENCRYPTION_BLOCKS;
                }

                fseek($fpIn, (int)$start);

                // Get the initialzation vector from the beginning of the file
                if (!$iv) {
                    $iv = fread($fpIn, 16);
                }

                if (!feof($fpIn)) {

                    // we have to read one block more for decrypting than for encrypting
                    $ciphertext = fread($fpIn, 16 * ($encryption_length + 1));
                    $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key_digest, OPENSSL_RAW_DATA, $iv);

                    if (!$plaintext) {
                        unlink($this->get_xcloner_path() . $dest);
                        if (is_object($this->logger)) {
                            $this->logger->error('Backup decryption failed, please check your provided Encryption Key.');
                        }
                        throw new \Exception("Backup decryption failed, please check your provided Encryption Key.");
                    }

                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);

                    if (!$this->verification) {
                        fwrite($fpOut, $plaintext);
                    }

                    $start = ftell($fpIn);

                    fclose($fpOut);

                    if (!feof($fpIn)) {
                        fclose($fpIn);
                        if ($this->verification || $recursive) {
                            unset($ciphertext);
                            unset($plaintext);
                            $this->decrypt_file($source, $dest, $key, $start, $iv, $recursive);
                        } else {
                            if (($iv) != base64_decode(base64_encode($iv))) {
                                throw new \Exception('Could not encode IV for transport');
                            }

                            return array(
                                "start" => $start,
                                "encryption_length" => $encryption_length,
                                "iv" => base64_encode($iv),
                                "target_file" => $dest,
                                "finished" => 0
                            );
                        }
                    }
                }
            } else {
                if (is_object($this->logger)) {
                    $this->logger->error('Unable to read source file for decryption');
                }
                throw new \Exception("Unable to read source file for decryption");
            }
        } else {
            if (is_object($this->logger)) {
                $this->logger->error('Unable to write destination file for decryption');
            }
            throw new \Exception("Unable to write destination file for decryption");
        }

        //we replace the original backup with the encrypted one
        if (!$keep_local && !$this->verification && copy(
                $this->get_xcloner_path() . $dest,
                $this->get_xcloner_path() . $source
            )) {
            unlink($this->get_xcloner_path() . $dest);
        }

        return array("target_file" => $dest, "finished" => 1);
    }

    public function get_xcloner_path()
    {
        if (is_object($this->xcloner_settings)) {
            return $this->xcloner_settings->get_xcloner_store_path() . DS;
        }

        return null;
    }
}


try {
    if (isset($argv[1])) {
        //Include autoloader, so Xcloner is defined.
        //SEE: https://github.com/watchfulli/xcloner-core/issues/3
        require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
        $xcloner_encryption = new Xcloner_Encryption(new Xcloner());

        if ($argv[1] == "-e") {
            $xcloner_encryption->encrypt_file($argv[2], $argv[2] . ".enc", $argv[4], 0, 0, false, true);
        } elseif ($argv[1] == "-d") {
            $xcloner_encryption->decrypt_file($argv[2], $argv[2] . ".dec", $argv[4], 0, 0, true);
        }
    }
} catch (\Exception $e) {
    echo "CAUGHT: " . $e->getMessage();
}
