<?php
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


# Register a custom 'composer' command
#
# $ wp run-tests
# Success: True

/**
 * Runs a command with composer
 *
 * <command>
 * : Command To run
 *
 * @when before_wp_load
 */
$command = function( $args, $assoc_args ) {
    $env = array('some_option' => 'aeiou');

$process = proc_open('vendor/bin/composer validate', [], $pipes, getcwd() . '/wp-content/plugins/xcloner', $env);

if (is_resource($process)) {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt

    fwrite($pipes[0], '<?php print_r($_ENV); ?>');
    fclose($pipes[0]);

    echo stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($process);

    echo "command returned $return_value\n";
}



};
WP_CLI::add_command( 'composer', $command );
