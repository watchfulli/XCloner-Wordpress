#!/usr/bin/env php
<?php

require_once __DIR__."/../lib/Dropbox/strict.php";

if (PHP_SAPI !== "cli") {
    throw new \Exception("This program was meant to be run from the command-line and not as a web app.  Bad value for PHP_SAPI.  Expected \"cli\", given \"".PHP_SAPI."\".");
}

// NOTE: You should be using Composer's global autoloader.  But just so these examples
// work for people who don't have Composer, we'll use the library's "autoload.php".
require_once __DIR__.'/../lib/Dropbox/autoload.php';

use \Dropbox as dbx;

if ($argc === 1) {
    echoHelp($argv[0]);
    die;
}

$remainingArgs = array();
$optionsAllowed = true;
$disable = false;
for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    if ($optionsAllowed && strpos($arg, "-") === 0) {
        if ($arg === "--") {
            $optionsAllowed = false;
            continue;
        }
        if ($arg === "--disable") {
            if ($disable) {
                fwrite(STDERR, "Option \"--disable\" used more than once.\n");
                die;
            }
            $disable = true;
        }
        else {
            fwrite(STDERR, "Unrecognized option \"$arg\".\n");
            fwrite(STDERR, "Run with no arguments for help\n");
        }
    }
    else {
        array_push($remainingArgs, $arg);
    }
}

if (count($remainingArgs) !== 3) {
    fwrite(STDERR, "Expecting exactly 3 non-option arguments, got ".count($remainingArgs)."\n");
    fwrite(STDERR, "Run with no arguments for help\n");
    die;
}

$appInfoFile = $remainingArgs[0];
$oauth1AccessToken = new dbx\OAuth1AccessToken($remainingArgs[1], $remainingArgs[2]);

try {
    list($appInfoJson, $appInfo) = dbx\AppInfo::loadFromJsonFileWithRaw($appInfoFile);
}
catch (dbx\AppInfoLoadException $ex) {
    fwrite(STDERR, "Error loading <app-info-file>: ".$ex->getMessage()."\n");
    die;
}

// Get an OAuth 2 access token.

$upgrader = new dbx\OAuth1Upgrader($appInfo, "examples-authorize", "en");
$oauth2AccessToken = $upgrader->createOAuth2AccessToken($oauth1AccessToken);
echo "OAuth 2 access token obtained.\n";

// Write out auth JSON.

$authArr = array(
    "access_token" => $oauth2AccessToken,
);

if (array_key_exists('host', $appInfoJson)) {
    $authArr['host'] = $appInfoJson['host'];
}

$json_options = 0;
if (defined('JSON_PRETTY_PRINT')) {
    $json_options |= JSON_PRETTY_PRINT;  // Supported in PHP 5.4+
}
echo json_encode($authArr, $json_options)."\n";

// Disable the OAuth 1 access token.

if ($disable) {
    $upgrader->disableOAuth1AccessToken($oauth1AccessToken);
    echo "OAuth 1 access token disabled.\n";
}

function echoHelp($command) {
    echo "\n";
    echo "Usage: $command <app-info-file> <oa1-access-token-key> <oa1-access-token-secret>\n";
    echo "\n";
    echo "<app-info-file>: A JSON file with information about your API app.  Example:\n";
    echo "\n";
    echo "  {\n";
    echo "    \"key\": \"Your Dropbox API app key\",\n";
    echo "    \"secret\": \"Your Dropbox API app secret\",\n";
    echo "  }\n";
    echo "\n";
    echo "  Get an API app key by registering with Dropbox:\n";
    echo "    https://dropbox.com/developers/apps\n";
    echo "\n";
    echo "<oa1-access-token-key>: The OAuth 1 access token key.\n";
    echo "\n";
    echo "<oa1-access-token-secret>: The OAuth 1 access token secret.\n";
    echo "\n";
    echo "Options:\n";
    echo "\n";
    echo "  --disable: After we get an OAuth 2 access token, tell the server to\n";
    echo "      disable the OAuth 1 access token.\n";
    echo "\n";
}
