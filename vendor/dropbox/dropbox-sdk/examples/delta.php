#!/usr/bin/env php
<?php

require_once __DIR__.'/helper.php';
use \Dropbox as dbx;

/* @var dbx\Client $client */
/* @var string $cursor */
list($client, $cursor) = parseArgs("delta", $argv,
    // Required parameters
    array(),
    // Optional parameters
    array(
        array("cursor", "The cursor returned by the previous delta call (optional)."),
    ));

$deltaPage = $client->getDelta($cursor);

$numAdds = 0;
$numRemoves = 0;
foreach ($deltaPage["entries"] as $entry) {
    list($lcPath, $metadata) = $entry;
    if ($metadata === null) {
        echo "- $lcPath\n";
        $numRemoves++;
    } else {
        echo "+ $lcPath\n";
        $numAdds++;
    }
}
echo "Num Adds: $numAdds\n";
echo "Num Removes: $numRemoves\n";
echo "Has More: ".$deltaPage["has_more"]."\n";
echo "Cursor: ".$deltaPage["cursor"]."\n";
