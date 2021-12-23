<?php
# Register a custom 'is-plugin-stable-tag-latest-tag' command
#
# $ wp is-tested-up-to
# Success: True
/**
 * Command to check if a plugin's stable tag is the latest version of WordPress or later.
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
    include_once(__DIR__ . '/CheckPlugin.php');
    //Path to readme, from args, or guess.
    $path = isset($assoc_args['readme']) ? $assoc_args['readme'] : dirname(__FILE__, 3).'/README.txt';
    //Create plugin object
    $plugin = new WPReadmeParser([
        'path' => $path
    ]);
    //Try to get latest version of WordPress
    try {
        $latestVersion = isset($assoc_args['version']) ? $assoc_args['version'] : WPPluginApi::getLatestWordPressVersion();
        //Worked? Cool, check if our plugin is out of date.
        $check = new CheckPlugin($plugin);
        if( $check->isStableTagLatestVersion($latestVersion) ){
            WP_CLI::success('Is Latest Version');
        } else {
            WP_CLI::error('Is Not Latest Version');
        }

    } catch (\Throwable $th) {
        //Failed? Bummer. Make error
        WP_CLI::error('Could not find latest version of WordPress via API.');

    }

};
WP_CLI::add_command( 'is-tested-up-to', $command );
