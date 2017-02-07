<?php

require_once __DIR__."/../lib/Dropbox/strict.php";

if (PHP_SAPI !== "cli") {
    throw new \Exception("This program was meant to be run from the command-line and not as a web app.  Bad value for PHP_SAPI.  Expected \"cli\", given \"".PHP_SAPI."\".");
}

// NOTE: You should be using Composer's global autoloader.  But just so these examples
// work for people who don't have Composer, we'll use the library's "autoload.php".
require_once __DIR__.'/../lib/Dropbox/autoload.php';
use \Dropbox as dbx;

/**
 * A helper function that checks for the correct number of command line arguments,
 * loads the auth-file, and creates a \Dropbox\Client object.
 *
 * It returns an array where the first element is the \Dropbox\Client object and the
 * rest of the elements are the arguments you wanted.
 */
function parseArgs($exampleName, $argv, $requiredParams = null, $optionalParams = null)
{
    if ($requiredParams === null) $requiredParams = array();
    if ($optionalParams === null) $optionalParams = array();

    $minArgs = 1 + count($requiredParams);
    $maxArgs = $minArgs + count($optionalParams);

    $programName = $argv[0];
    $args = \array_slice($argv, 1);

    // If no args.  Print help message.
    if (count($args) === 0) {
        // Construct the param list for the "Usage" line.
        $paramSpec = "";
        foreach ($requiredParams as $p) {
            $paramSpec .= " ".$p[0];
        }
        if (count($optionalParams) > 0) {
            $paramSpec .= " [";
            foreach ($optionalParams as $p) {
                $paramSpec .= " ".$p[0];
            }
            $paramSpec .= " ]";
        }

        echo "\n";
        echo "Usage: $programName auth-file$paramSpec\n";
        echo "\n";

        // Print out help for each param.
        printParamHelp("auth-file",
            "A file with authorization information.  You can use the \"examples/authorize.php\" ".
            "program to generate this file.");
        foreach (array_merge($requiredParams, $optionalParams) as $param) {
            list($paramName, $paramDesc) = $param;
            printParamHelp($paramName, $paramDesc);
        }

        echo "\n";
        echo "OPTIONS:\n";
        echo "\n";
        echo "--locale=...   (example: --locale=fr)\n";
        echo "     The locale you want the Dropbox API to use for localized strings.\n";
        echo "\n";
        exit(0);
    }

    $locale = null;

    // Parse out the option args.
    $nonOptionArgs = array();
    for ($i = 0; $i < count($args); $i++) {
        $arg = $args[$i];
        if ($arg === "-" || $arg === "--") {
            // No more options.  Put the rest on the list of non-option args.
            for ($i++; $i < count($args); $i++) {
                \array_push($nonOptionArgs, $args[$i]);
            }
            break;
        }
        if (startsWith($arg, "-")) {
            if (startsWith($arg, "--")) {
                $option = substr($arg, 2);
            } else {
                $option = substr($arg, 1);
            }
            $equalPos = \strpos($option, "=");
            if ($equalPos === false) {
                $optionName = $option;
                $optionArg = null;
            } else {
                $optionName = \substr($option, 0, $equalPos);
                $optionArg = \substr($option, $equalPos+1);
            }

            if ($optionName === 'locale') {
                if ($optionArg === null) {
                    fwrite(STDERR, "\"locale\" option requires an argument.\n");
                    fwrite(STDERR, "Run with no arguments for help.\n");
                    die;
                }
                $locale = $optionArg;
                if (count($locale) === 0) {
                    fwrite(STDERR, "\"locale\" must not be empty.\n");
                    fwrite(STDERR, "Run with no arguments for help.\n");
                    die;
                }
            }
            else {
                fwrite(STDERR, "Unknown option: \"$optionName\".\n");
                fwrite(STDERR, "Run with no arguments for help.\n");
                die;
            }
        }
        else {
            \array_push($nonOptionArgs, $arg);
        }
    }

    $givenArgs = count($nonOptionArgs);

    // Make sure the argument count is compatible with the parameter count.
    if ($minArgs === $maxArgs) {
        if (count($nonOptionArgs) !== $minArgs) {
            fwrite(STDERR, "Expecting exactly $minArgs non-option arguments, got $givenArgs.\n");
            fwrite(STDERR, "Run with no arguments for help.\n");
            die;
        }
    }
    else {
        if ($givenArgs < $minArgs) {
            fwrite(STDERR, "Expecting at least $minArgs non-option arguments, got $givenArgs.\n");
            fwrite(STDERR, "Run with no arguments for help.\n");
            die;
        }
        else if ($givenArgs > $maxArgs) {
            fwrite(STDERR, "Expecting at most $maxArgs non-option arguments, got $givenArgs.\n");
            fwrite(STDERR, "Run with no arguments for help.\n");
            die;
        }
    }

    try {
        list($accessToken, $host) = dbx\AuthInfo::loadFromJsonFile($nonOptionArgs[0]);
    }
    catch (dbx\AuthInfoLoadException $ex) {
        fwrite(STDERR, "Error loading <auth-file>: ".$ex->getMessage()."\n");
        die;
    }

    $client = new dbx\Client($accessToken, "examples-$exampleName", $locale, $host);

    // Fill in the extra/optional arg slots with nulls.
    $ret = array_slice($nonOptionArgs, 1);
    while (count($ret) < $maxArgs) {
        array_push($ret, null);
    }

    // Return the args they need, plus the $client object in front.
    array_unshift($ret, $client);
    return $ret;
}

function startsWith($s, $prefix)
{
    return (\substr_compare($s, $prefix, 0, count($prefix)) === 0);
}

function printParamHelp($paramName, $paramDesc)
{
    $wordWrapWidth = 70;
    $lines = wordwrap("$paramName: $paramDesc", $wordWrapWidth, "\n  ");
    echo "$lines\n\n";
}
