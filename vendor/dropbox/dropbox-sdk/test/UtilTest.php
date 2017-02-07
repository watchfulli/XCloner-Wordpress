<?php

require_once __DIR__.'/../lib/Dropbox/strict.php';

use \Dropbox as dbx;

class UtilTest extends PHPUnit_Framework_TestCase
{
    function testQ()
    {
        $this->assertEquals(dbx\Util::q(""), "\"\"");
        $this->assertEquals(dbx\Util::q("abcd"), "\"abcd\"");
        $this->assertEquals(dbx\Util::q(" \" \r \n \\ \x00 \x7e \x7f \xff "),
            '" \" \r \n \\\\ \x00 ~ \x7f \xff "');
    }
}
