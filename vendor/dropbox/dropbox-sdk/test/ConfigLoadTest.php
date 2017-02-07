<?php

require_once __DIR__.'/../lib/Dropbox/strict.php';

use \Dropbox as dbx;

class ConfigLoadTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
        @unlink("test.json");
    }

    function testMissingAppJson()
    {
        $this->setExpectedException('\Dropbox\AppInfoLoadException');
        dbx\AppInfo::loadFromJsonFile("missing.json");
    }

    function testBadAppJson()
    {
        $this->setExpectedException('\Dropbox\AppInfoLoadException');
        file_put_contents("test.json", "Not JSON.  At all.");
        dbx\AppInfo::loadFromJsonFile("test.json");
    }

    function testNonHashAppJson()
    {
        $this->setExpectedException('\Dropbox\AppInfoLoadException');
        file_put_contents("test.json", json_encode(123, true));
        dbx\AppInfo::loadFromJsonFile("test.json");
    }

    function testBadAppJsonFields()
    {
        $correct = array(
            "key" => "an_app_key",
            "secret" => "an_app_secret",
        );

        // check that we detect every missing field
        foreach ($correct as $key => $value) {
            $tmp = $correct;
            unset($tmp[$key]);

            file_put_contents("test.json", json_encode($tmp, true));

            try {
                dbx\AppInfo::loadFromJsonFile("test.json");
                $this->fail("Expected exception");
            }
            catch (dbx\AppInfoLoadException $e) {
                // Expecting this exception.
            }
        }

        // check that we detect non-string fields
        foreach ($correct as $key => $value) {
            $tmp = $correct;
            $tmp[$key] = 123;

            file_put_contents("test.json", json_encode($tmp, true));

            try {
                dbx\AppInfo::loadFromJsonFile("test.json");
                $this->fail("Expected exception");
            }
            catch (dbx\AppInfoLoadException $e) {
                // Expecting this exception.
            }
        }
    }

    function testAppJsonServer()
    {
        $correct = array(
            "key" => "an_app_key",
            "secret" => "an_app_secret",
            "access_type" => "AppFolder",
            "auth_host" => "www.dropbox-auth.com",
            "host_suffix" => ".droppishbox.com",
        );

        $str = json_encode($correct, true);
        self::tryAppJsonServer($str);
        self::tryAppJsonServer("\xEF\xBB\xBF".$str);  // UTF-8 byte order mark
    }

    function tryAppJsonServer($str)
    {
        file_put_contents("test.json", $str);
        $appInfo = dbx\AppInfo::loadFromJsonFile("test.json");
        $this->assertEquals($appInfo->getHost()->getContent(), "content.droppishbox.com");
        $this->assertEquals($appInfo->getHost()->getApi(), "api.droppishbox.com");
        $this->assertEquals($appInfo->getHost()->getWeb(), "www.dropbox-auth.com");
    }

    function testMissingAuthJson()
    {
        $this->setExpectedException('\Dropbox\AuthInfoLoadException');
        dbx\AuthInfo::loadFromJsonFile("missing.json");
    }

    function testBadAuthJson()
    {
        $this->setExpectedException('\Dropbox\AuthInfoLoadException');
        file_put_contents("test.json", "Not JSON.  At all.");
        dbx\AuthInfo::loadFromJsonFile("test.json");
    }

    function testNonHashAuthJson()
    {
        $this->setExpectedException('\Dropbox\AuthInfoLoadException');
        file_put_contents("test.json", json_encode(123, true));
        dbx\AuthInfo::loadFromJsonFile("test.json");
    }

    function testBadAuthJsonFields()
    {
        $minimal = array(
            "access_token" => "an_access_token",
        );

        // check that we detect every missing field
        foreach ($minimal as $key => $value) {
            $tmp = $minimal;
            unset($tmp[$key]);

            file_put_contents("test.json", json_encode($tmp, true));

            try {
                dbx\AuthInfo::loadFromJsonFile("test.json");
                $this->fail("Expected exception");
            }
            catch (dbx\AuthInfoLoadException $e) {
                // Expecting this exception.
            }
        }

        $correct = array(
            "access_token" => "an_access_token",
            "auth_host" => "auth.test-server.com",
            "host_suffix" => ".test-server.com",
        );

        // check that we detect non-string fields
        foreach ($correct as $key => $value) {
            $tmp = $correct;
            $tmp[$key] = 123;

            file_put_contents("test.json", json_encode($tmp, true));

            try {
                dbx\AuthInfo::loadFromJsonFile("test.json");
                $this->fail("Expected exception");
            }
            catch (dbx\AuthInfoLoadException $e) {
                // Expecting this exception.
            }
        }
    }

}
