<?php

use ChrisWhite\B2\Client;
use Mhetreramesh\Flysystem\BackblazeAdapter as Backblaze;
use \ChrisWhite\B2\File;
use \League\Flysystem\Config;

class BackblazeAdapterTests extends PHPUnit_Framework_TestCase
{
    public function backblazeProvider()
    {
        $mock = $this->prophesize('ChrisWhite\B2\Client');
        return [
            [new Backblaze($mock->reveal(), 'my_bucket'), $mock],
        ];
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testWrite($adapter, $mock)
    {
        $mock->upload(["BucketName" => "my_bucket", "FileName" => "something", "Body" => "contents"])->willReturn(new File('something','','','',''), false);
        $result = $adapter->write('something', 'contents', new Config());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
    }
}