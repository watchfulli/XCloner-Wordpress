<?php
/**
 * Created by PhpStorm.
 * User: thinkovi
 * Date: 2018-11-27
 * Time: 12:11
 */

namespace XCloner;

class Xcloner_Encryption
{
    const FILE_ENCRYPTION_BLOCKS = 1024*1024;

    public function __construct()
    {

    }

    /**
    * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
    *
    * @param string $source Path to file that should be encrypted
    * @param string $key    The key used for the encryption
    * @param string $dest   File name where the encryped file should be written to.
    * @return string|false  Returns the file name that has been created or FALSE if an error occured
    */
    public static function encryptFile($source, $dest, $key = "test", $start = 0, $iv = 0)
    {
        //$key = substr(sha1($key, true), 0, 16);
        $key_digest = openssl_digest ($key, "md5", true);
        if(!$iv) {
            $iv = openssl_random_pseudo_bytes(16);
        }

        if( !$start ) {
            $fpOut = fopen($dest, 'w');
        }else{
            $fpOut = fopen($dest, 'a');
        }

        if ( $fpOut ) {

            // Put the initialization vector to the beginning of the file
            if(!$start) {
                fwrite($fpOut, $iv);
            }

            if ($fpIn = fopen($source, 'rb')) {

                fseek($fpIn, $start);

                if (!feof($fpIn)) {

                    $plaintext = fread($fpIn, 16 * self::FILE_ENCRYPTION_BLOCKS);
                    $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key_digest, OPENSSL_RAW_DATA, $iv);

                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);

                    fwrite($fpOut, $ciphertext);

                    $start = ftell($fpIn);

                    fclose($fpOut );

                    if(!feof($fpIn)) {
                        fclose($fpIn);
                        //echo "\n NEW:".$key.md5($iv);
                        self::encryptFile($source, $dest, $key, $start, $iv);
                    }

                }
            } else {
                throw new \Exception("Unable to read source file for encryption");
            }
        } else {
            throw new \Exception("Unable to write destination file for encryption");
        }

        return $dest;
    }

    /**
     * Dencrypt the passed file and saves the result in a new file, removing the
     * last 4 characters from file name.
     *
     * @param string $source Path to file that should be decrypted
     * @param string $key    The key used for the decryption (must be the same as for encryption)
     * @param string $dest   File name where the decryped file should be written to.
     * @return string|false  Returns the file name that has been created or FALSE if an error occured
     */
    function decryptFile($source, $dest, $key = "test", $start = 0, $iv = 0)
    {
        //$key = substr(sha1($key, true), 0, 16);
        $key_digest = openssl_digest ($key, "md5", true);

        if( !$start ) {
            $fpOut = fopen($dest, 'w');
        }else{
            $fpOut = fopen($dest, 'a');
        }

        if ( $fpOut ) {
            if ($fpIn = fopen($source, 'rb')) {

                fseek($fpIn, $start);

                // Get the initialzation vector from the beginning of the file
                if(!$iv) {
                    $iv = fread($fpIn, 16);
                }

                if (!feof($fpIn)) {

                    // we have to read one block more for decrypting than for encrypting
                    $ciphertext = fread($fpIn, 16 * (self::FILE_ENCRYPTION_BLOCKS + 1));
                    $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key_digest, OPENSSL_RAW_DATA, $iv);

                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);

                    fwrite($fpOut, $plaintext);

                    $start = ftell($fpIn);

                    fclose($fpOut );

                    if(!feof($fpIn)) {
                        fclose($fpIn);
                        self::decryptFile($source, $dest, $key, $start, $iv);
                    }

                }
            } else {
                throw new \Exception("Unable to read source file for decryption");
            }
        } else {
            throw new \Exception("Unable to write destination file for decryption");
        }

        return $dest;
    }

}

try {
    if ($argv[1] == "-e") {
        Xcloner_Encryption::encryptFile($argv[2], $argv[2] . ".enc");
    } elseif ($argv[1] == "-d") {
        Xcloner_Encryption::decryptFile($argv[2], $argv[2] . ".out");
    }
}catch(\Exception $e) {
    echo "CAUGHT: " . $e->getMessage();
}
