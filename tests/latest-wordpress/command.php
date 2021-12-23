<?php
# Register a custom 'latest-wordpress' command
#
# $ wp latest-wordpress
# Success: True
/**
 * Command to get latest version of WordPress via API
 * @when before_wp_load
 */
$command = function( $args, $assoc_args ) {
    //Include classes
    include_once(dirname(__FILE__ ,2 ) . '/WPPluginApi.php');

    //Try to get latest version of WordPress
    try {
        $latestWordPressVersion = isset($assoc_args['version']) ? $assoc_args['version'] : WPPluginApi::getLatestWordPressVersion();
        WP_CLI::success($latestWordPressVersion);
    } catch (\Throwable $th) {
        //Failed? Bummer. Make error
        WP_CLI::error('Could not find latest version of WordPress via API.');

    }

};
WP_CLI::add_command( 'latest-wordpress', $command );
