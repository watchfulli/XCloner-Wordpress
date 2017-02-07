#!/usr/bin/env php
<?php

require_once __DIR__.'/helper.php';
use \Dropbox as dbx;

/* @var dbx\Client $client */
/* @var string $dropboxPath */
list($client, $dropboxPath) = parseArgs("get-metadata", $argv, array(
        array("dropbox-path", "The path (on Dropbox) that you want metadata for."),
    ));

$pathError = dbx\Path::findError($dropboxPath);
if ($pathError !== null) {
    fwrite(STDERR, "Invalid <dropbox-path>: $pathError\n");
    die;
}

$metadata = $client->getMetadataWithChildren($dropboxPath);

if ($metadata === null) {
    fwrite(STDERR, "No file or folder at that path.\n");
    die;
}

// If it's a folder, remove the 'contents' list from $metadata; print that stuff out after.
$children = null;
if ($metadata['is_dir']) {
    $children = $metadata['contents'];
    unset($metadata['contents']);
}

print_r($metadata);

if ($children !== null && count($children) > 0) {
    print "Children:\n";
    foreach ($children as $child) {
        $name = dbx\Path::getName($child['path']);
        if ($child['is_dir']) $name = "$name/";  // Put a "/" after folder names.
        print "- $name\n";
    }
}
