<?php

echo shell_exec ("composer require watchfulli/xcloner-core:dev-restore");

$file = __DIR__.'/vendor.phar';
$dest = __DIR__ . '/vendor.build.txt';

if (file_exists($file)) {
    unlink($file);
}
    
$phar2 = new Phar($file, 0, 'vendor.phar');

// add all files in the project, only include php files
$phar2->buildFromIterator(
    new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__.'/vendor/')
     ),
    __DIR__
);

 $phar2->setStub($phar2->createDefaultStub('vendor/autoload.php', 'vendor/autoload.php'));


 if(copy($file, $dest)) {
    unlink($file);
 }
