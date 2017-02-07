#! /usr/bin/env php
<?php
/*
Test to see if the PHP installation performs basic SSL security checks.
-----------------------------------------------------------------------------
If any of the tests fail, it means your PHP installation uses an insecure SSL
implementation.  An attacker can view and manipulate your communication with
the Dropbox API servers.  If this is the case, try upgrading your version of
PHP.
-----------------------------------------------------------------------------
You can run this script in one of three ways:
1. Run on the command-line: php test-ssl.php
2. Put the SDK folder in your web server's folder and view this page.
3. Add the following code to a test page on your site:
    <?php
        require_once "path-to-dropbox-sdk/lib/Dropbox/autoload.php"
        echo "<pre>\n"
        Dropbox\SSLTester::test();
        echo "</pre>\n"
    ?>
*/

require_once __DIR__."/../lib/Dropbox/strict.php";

// NOTE: You should be using Composer's global autoloader.  But just so these examples
// work for people who don't have Composer, we'll use the library's "autoload.php".
require_once __DIR__.'/../lib/Dropbox/autoload.php';

use \Dropbox as dbx;

if (PHP_SAPI === "cli") {
    // Command-line test.
    $passed = dbx\SSLTester::test();
    if (!$passed) exit(1);
}
else {
    // Web test.
    echo "<pre>\n";
    dbx\SSLTester::test();
    echo "</pre>\n";
}
