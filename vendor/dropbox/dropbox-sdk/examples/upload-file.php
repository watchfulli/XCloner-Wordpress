#!/usr/bin/env php
<?php

require_once __DIR__.'/helper.php';
use \Dropbox as dbx;

/* @var dbx\Client $client */
/* @var string $sourcePath */
/* @var string $dropboxPath */
list($client, $sourcePath, $dropboxPath) = parseArgs("upload-file", $argv, array(
        array("source-path", "A path to a local file or a URL of a resource."),
        array("dropbox-path", "The path (on Dropbox) to save the file to."),
    ));

$pathError = dbx\Path::findErrorNonRoot($dropboxPath);
if ($pathError !== null) {
    fwrite(STDERR, "Invalid <dropbox-path>: $pathError\n");
    die;
}

$size = null;
if (\stream_is_local($sourcePath)) {
    $size = \filesize($sourcePath);
}

$fp = fopen($sourcePath, "rb");
$metadata = $client->uploadFile($dropboxPath, dbx\WriteMode::add(), $fp, $size);
fclose($fp);

print_r($metadata);
