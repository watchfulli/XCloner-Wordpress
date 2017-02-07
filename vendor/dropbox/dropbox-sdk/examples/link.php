#!/usr/bin/env php
<?php

require_once __DIR__.'/helper.php';
use \Dropbox as dbx;

/* @var dbx\Client $client */
/* @var string $dropboxPath */
list($client, $dropboxPath) = parseArgs("link", $argv, array(
        array("dropbox-path", "The path (on Dropbox) to create a link for."),
    ));

$pathError = dbx\Path::findError($dropboxPath);
if ($pathError !== null) {
    fwrite(STDERR, "Invalid <dropbox-path>: $pathError\n");
    die;
}

$link = $client->createShareableLink($dropboxPath);

print($link."\n");
