<?php
# Register a custom 'is-plugin-stable-tag-latest-tag' command
#
# $ wp is-plugin-stable-tag-latest-tag
# Success: True

/**
 * Command to check if a plugin's stable tag is the latest version of WordPress or later.
 *
 * [--readme]
 * : Path to readme.txt file.
 *
 * @when before_wp_load
 */
$command = function( $args, $assoc_args ) {
    /**
     * Get latest version of WordPress, from WordPress.org
     */
    $getLatestVersion = function(){
        $api = json_decode(file_get_contents('https://api.wordpress.org/core/version-check/1.7/'), true);
        $latestVersion = $api['offers'][0]['current'];
        return $latestVersion;
    };

    //Include classes
    include_once(__DIR__ . '/WPReadmeParser.php');
    include_once(__DIR__ . '/CheckPlugin.php');
    //Path to readme, from args, or guess.
    $path = isset($assoc_args['readme']) ? $assoc_args['readme'] : dirname(__FILE__, 3).'/README.txt';
    //Create plugin object
    $plugin = new WPReadmeParser([
        'path' => $path
    ]);
    //Try to get latest version of WordPress
    try {
        $latestVersion = $getLatestVersion();

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
WP_CLI::add_command( 'is-plugin-stable-tag-latest-tag', $command );
