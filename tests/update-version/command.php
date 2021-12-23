<?php
# Register a custom 'update-version' command
#
# $ wp update-version
# Success: True
/**
 * Command to update version and tested up to in README.txt and xclone.php
 *
 * [--readme]
 * : Path to readme.txt file.
 *
 * [--version]
 * : Latest version of WordPress.
 *
 * @when before_wp_load
 */
$command = function( $args, $assoc_args ) {
    //Include classes
    include_once(dirname(__FILE__ ,2 ) . '/WPPluginApi.php');
    include_once(dirname(__FILE__ ,2 ) . '/WPReadmeParser.php');
    //Path to readme, from args, or guess.
    $path = isset($assoc_args['readme']) ? $assoc_args['readme'] : dirname(__FILE__, 3).'/README.txt';
    //Create plugin object
    $plugin = new WPReadmeParser([
        'path' => $path
    ]);
    //Try to get latest version of WordPress
    try {
        $latestWordPressVersion = file_exists(dirname(__FILE__,3).'/wpv.txt') ? file_get_contents(dirname(__FILE__,3).'/wpv.txt') : WPPluginApi::getLatestWordPressVersion();
        //Find Current Values
        $testedUpTo = $plugin->metadata[WPReadmeParser::TESTED_UP_TO];
        $currentVersion = $plugin->metadata[WPReadmeParser::STABLE_TAG];
        //Explode current version and increase last digit by 1
        $newVersion = explode( '.',$currentVersion);
        $newVersion[array_key_last($newVersion)] = (int)$newVersion[array_key_last($newVersion)] + 1;
        $newVersion= implode( '.',$newVersion);
        //Get current README and update tested up to and stable tag
        $handle = fopen( $path, 'r+'  );
        if( ! $handle ){
            throw new \Exception('Could not open README.txt');
        }
        $currentTxt = file_get_contents($path);
        $newText = str_replace("Stable tag: $currentVersion", "Stable tag: $newVersion", $currentTxt);
        $newText = str_replace("Tested up to: $testedUpTo", "Tested up to: $latestWordPressVersion", $newText);
        fwrite($handle,$newText);
        fclose($handle);
        //Now change the plugin's version in xlconer.php
        $path = dirname(__FILE__, 3).'/xcloner.php';
        $handle = fopen($path, 'r+'  );
        if( ! $handle ){
            throw new \Exception('Could not open xcloner.php');
        }
        $currentTxt = file_get_contents($path);
        $newText = str_replace("Version: $currentVersion", "Version: $newVersion", $currentTxt);
        fwrite($handle,$newText);
        fclose($handle);

    } catch (\Throwable $th) {
        //Failed? Bummer. Make error
        WP_CLI::error('Could not find latest version of WordPress via API.');

    }

};
WP_CLI::add_command( 'update-version', $command );
