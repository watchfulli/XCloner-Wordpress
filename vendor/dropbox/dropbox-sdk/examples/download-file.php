#!/usr/bin/env php
<?php

require_once __DIR__.'/helper.php';
use \Dropbox as dbx;

/* @var dbx\Client $client */
/* @var string $dropboxPath */
/* @var string $localPath */
list($client, $dropboxPath, $localPath) = parseArgs("download-file", $argv,
    // Required parameters
    array(
        array("dropbox-path", "The path of the file (on Dropbox) to download."),
        array("local-path", "The local path to save the downloaded file contents to."),
    ));

$pathError = dbx\Path::findErrorNonRoot($dropboxPath);
if ($pathError !== null) {
    fwrite(STDERR, "Invalid <dropbox-path>: $pathError\n");
    die;
}

$metadata = $client->getFile($dropboxPath, fopen($localPath, "wb"));
if ($metadata === null) {
    fwrite(STDERR, "File not found on Dropbox.\n");
    die;
}

print_r($metadata);
echo "File contents written to \"$localPath\"\n";
