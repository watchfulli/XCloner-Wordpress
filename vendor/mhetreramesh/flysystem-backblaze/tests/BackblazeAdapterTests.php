<?php

use BackblazeB2\Client;
use Mhetreramesh\Flysystem\BackblazeAdapter as Backblaze;
use BackblazeB2\File;
use \League\Flysystem\Config;

class BackblazeAdapterTests extends PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $fs_mock;

    /**
     * @var vfsStreamFile
     */
    private $file_mock;

    private function fileSetUp() {
        $this->fs_mock = \org\bovigo\vfs\vfsStream::setup();
        $this->file_mock = new \org\bovigo\vfs\vfsStreamFile('filename.ext');
        $this->fs_mock->addChild($this->file_mock);
    }

    public function backblazeProvider()
    {
        $mock = $this->prophesize('BackblazeB2\Client');
        return [
            [new Backblaze($mock->reveal(), 'my_bucket'), $mock],
        ];
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testHas($adapter, $mock)
    {
        $mock->fileExists(["BucketName" => "my_bucket", "FileName" => "something"])->willReturn(true);
        $result = $adapter->has('something');
        $this->assertTrue($result);
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

    /**
     * @dataProvider  backblazeProvider
     */
    public function testWriteStream($adapter, $mock)
    {
        $mock->upload(["BucketName" => "my_bucket", "FileName" => "something", "Body" => "contents"])->willReturn(new File('something','','','',''), false);
        $result = $adapter->writeStream('something', 'contents', new Config());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testUpdate($adapter, $mock)
    {
        $mock->upload(["BucketName" => "my_bucket", "FileName" => "something", "Body" => "contents"])->willReturn(new File('something','','','',''), false);
        $result = $adapter->update('something', 'contents', new Config());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testUpdateStream($adapter, $mock)
    {
        $mock->upload(["BucketName" => "my_bucket", "FileName" => "something", "Body" => "contents"])->willReturn(new File('something','','','',''), false);
        $result = $adapter->updateStream('something', 'contents', new Config());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testRead($adapter, $mock)
    {
        $file = new File('something','something4','','','','','my_bucket');
        $mock->getFile(["BucketName" => "my_bucket", "FileName" => "something"])->willReturn($file, false);
        $mock->download(["FileId" => "something"])->willReturn($file, false);
        $result = $adapter->read('something');
        $this->assertEquals(['contents' => $file], $result);
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testReadStream($adapter, $mock)
    {
        //$mock->fileExists(["BucketName" => "my_bucket", "FileName" => "something"])->willReturn(true);
        $result = $adapter->readStream('something');
        $this->assertFalse($result);
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testRename($adapter, $mock)
    {
        //$mock->fileExists(["BucketName" => "my_bucket", "FileName" => "something"])->willReturn(true);
        $result = $adapter->rename('something', 'something_new');
        $this->assertFalse($result);
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testGetMetaData($adapter, $mock)
    {
        //$mock->fileExists(["BucketName" => "my_bucket", "FileName" => "something"])->willReturn(true);
        $result = $adapter->getMetadata('something');
        $this->assertFalse($result);
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testGetMimetype($adapter, $mock)
    {
        //$mock->fileExists(["BucketName" => "my_bucket", "FileName" => "something"])->willReturn(true);
        $result = $adapter->getMimetype('something');
        $this->assertFalse($result);
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testCopy($adapter, $mock)
    {
        $this->fileSetUp();
        $mock->upload(["BucketName" => "my_bucket", "FileName" => "something_new", "Body" => ""])->willReturn(new File('something_new','','','',''), false);
        $result = $adapter->copy($this->file_mock->url(), 'something_new');
        $this->assertObjectHasAttribute('id', $result, 'something_new');
    }

    /**
     * @dataProvider  backblazeProvider
     */
    public function testListContents($adapter, $mock)
    {
        $mock->listFiles(["BucketName" => "my_bucket"])->willReturn([new File('random_id', 'file1.txt'), new File('random_id', 'some_folder/file2.txt'), new File('random_id', 'some_folder/another_folder/file3.txt')]);
        $normalized_files = [
            [
                'type' => 'file',
                'path' => 'file1.txt',
                'timestamp' => false,
                'size' => NULL,
            ],
            [
                'type' => 'file',
                'path' => 'some_folder/file2.txt',
                'timestamp' => false,
                'size' => NULL,
            ],
            [
                'type' => 'file',
                'path' => 'some_folder/another_folder/file3.txt',
                'timestamp' => false,
                'size' => NULL,
            ],
        ];
        $result1 = $adapter->listContents('', false);
        $this->assertEquals([$normalized_files[0]], $result1);
        $result2 = $adapter->listContents('some_folder', false);
        $this->assertEquals([$normalized_files[1]], $result2);
        $result3 = $adapter->listContents('', true);
        $this->assertEquals($normalized_files, $result3);
        $result3 = $adapter->listContents('some_folder', true);
        $this->assertEquals([$normalized_files[1], $normalized_files[2]], $result3);

        $this->expectException(\InvalidArgumentException::class);
        $adapter->listContents(false, false);
        $adapter->listContents(false, 'haha');
        $adapter->listContents('', 'haha');
    }
}