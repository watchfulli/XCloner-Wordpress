<?php


<?php
# Register a custom 'check' command
#
# $ wp check
# Success: True
/**
 * Command to check if a plugin's stable tag is the latest version of WordPress or later.
 *

 * @when before_wp_load
 */
$command = function( $args, $assoc_args ) {


};
WP_CLI::add_command( 'check', $command );
