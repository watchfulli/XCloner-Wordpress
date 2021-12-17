<?php

$api = json_decode(file_get_contents('https://api.wordpress.org/core/version-check/1.7/'), true);
$latestVersion = $api['offers'][0]['current'];

include_once(__DIR__ . '/WPReadmeParser.php');
include_once(__DIR__ . '/CheckPlugin.php');

$plugin = new WPReadmeParser([
    'path' => dirname(__FILE__, 2).'/README.txt',
]);
$check = new CheckPlugin($plugin);
var_dump($check->isStableTagLatestVersion($latestVersion));
