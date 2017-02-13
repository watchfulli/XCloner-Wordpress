<?php

if(!isset($argv));
	die('Access denied from web.');

$phar2 = new Phar('restore/vendor.phar', 0, 'vendor.phar');
// add all files in the project, only include php files
//$phar2->buildFromDirectory(dirname(__FILE__) . '/vendor', '/\.php$/');
$phar2->buildFromIterator(
    new RecursiveIteratorIterator(
     new RecursiveDirectoryIterator('vendor/')),
    '/');

$phar2->setStub($phar2->createDefaultStub('vendor/autoload.php', 'vendor/autoload.php'));
