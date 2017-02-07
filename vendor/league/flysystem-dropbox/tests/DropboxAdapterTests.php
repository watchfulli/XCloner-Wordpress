<?php

use Dropbox\Client;
use Dropbox\Exception_BadResponseCode;
use League\Flysystem\Config;
use League\Flysystem\Dropbox\DropboxAdapter as Dropbox;
use Prophecy\Argument;

class DropboxTests extends PHPUnit_Framework_TestCase
{
    public function dropboxProvider()
    {
        $mock = $this->prophesize('Dropbox\Client');

        return [
            [new Dropbox($mock->reveal(), 'prefix'), $mock],
        ];
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testWrite($adapter, $mock)
    {
        $mock->uploadFileFromString(Argument::any(), Argument::any(), Argument::any())->willReturn([
            'is_dir'   => false,
            'modified' => '10 September 2000',
            'path' => '/prefix/something',
        ], false);

        $result = $adapter->write('something', 'contents', new Config());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
        $this->assertFalse($adapter->write('something', 'something', new Config()));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testUpdate(Dropbox $adapter, $mock)
    {
        $mock->uploadFileFromString(Argument::any(), Argument::any(), Argument::any())->willReturn([
            'is_dir'   => false,
            'modified' => '10 September 2000',
            'path' => '/prefix/something'
        ], false);

        $result = $adapter->update('something', 'contents', new Config());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
        $this->assertFalse($adapter->update('something', 'something', new Config()));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testWriteStream(Dropbox $adapter, $mock)
    {
        $mock->uploadFile(Argument::any(), Argument::any(), Argument::any(), null)->willReturn([
            'is_dir'   => false,
            'modified' => '10 September 2000',
            'path' => '/prefix/something'
        ], false);

        $result = $adapter->writeStream('something', tmpfile(), new Config());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
        $this->assertFalse($adapter->writeStream('something', tmpfile(), new Config()));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testUpdateStream(Dropbox $adapter, $mock)
    {
        $mock->uploadFile(Argument::any(), Argument::any(), Argument::any(), null)->willReturn([
            'is_dir'   => false,
            'modified' => '10 September 2000',
            'path' => '/prefix/something'
        ], false);

        $result = $adapter->updateStream('something', tmpfile(), new Config());
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
        $this->assertFalse($adapter->updateStream('something', tmpfile(), new Config()));
    }

    public function metadataProvider()
    {
        return [
            ['getMetadata'],
            ['getMimetype'],
            ['getTimestamp'],
            ['getSize'],
            ['has'],
        ];
    }

    /**
     * @dataProvider  metadataProvider
     */
    public function testMetadataCalls($method)
    {
        $mock = $this->prophesize('Dropbox\Client');
        $mock->getMetadata('/one')->willReturn([
            'is_dir'   => false,
            'modified' => '10 September 2000',
            'path' => '/one'
        ], false);

        $adapter = new Dropbox($mock->reveal());
        $this->assertInternalType('array', $adapter->{$method}('one', 'two'));
        $this->assertFalse($adapter->{$method}('one', 'two'));
    }

    public function testMetadataFileWasMovedFailure()
    {
        $mock = $this->prophesize('Dropbox\Client');
        $mock->getMetadata('/one')->willThrow(new Exception_BadResponseCode('ERROR', 301));

        $adapter = new Dropbox($mock->reveal());
        $this->assertFalse($adapter->has('one'));
    }

    public function testMetadataFileWasNotMovedFailure()
    {
        $this->setExpectedException('Dropbox\Exception_BadResponseCode');
        $mock = $this->prophesize('Dropbox\Client');
        $mock->getMetadata('/one')->willThrow(new Exception_BadResponseCode('ERROR', 500));

        (new Dropbox($mock->reveal()))->has('one');
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testRead($adapter, $mock)
    {
        $stream = tmpfile();
        fwrite($stream, 'something');
        $mock->getFile(Argument::any(), Argument::any())->willReturn($stream, false);
        $this->assertInternalType('array', $adapter->read('something'));
        $this->assertFalse($adapter->read('something'));
        fclose($stream);
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testReadStream(Dropbox $adapter, $mock)
    {
        $stream = tmpfile();
        fwrite($stream, 'something');
        $mock->getFile(Argument::any(), Argument::any())->willReturn($stream, false);
        $this->assertInternalType('array', $adapter->readStream('something'));
        $this->assertFalse($adapter->readStream('something'));
        fclose($stream);
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testDelete(Dropbox $adapter, $mock)
    {
        $mock->delete('/prefix/something')->willReturn(['is_deleted' => true]);
        $this->assertTrue($adapter->delete('something'));
        $this->assertTrue($adapter->deleteDir('something'));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testCreateDir(Dropbox $adapter, $mock)
    {
        $mock->createFolder('/prefix/fail/please')->willReturn(null);
        $mock->createFolder('/prefix/pass/please')->willReturn([
            'is_dir' => true,
            'path'   => '/prefix/pass/please',
        ]);
        $this->assertFalse($adapter->createDir('fail/please', new Config()));
        $expected = ['path' => 'pass/please', 'type' => 'dir'];
        $this->assertEquals($expected, $adapter->createDir('pass/please', new Config()));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testListContents(Dropbox $adapter, $mock)
    {
        $mock->getMetadataWithChildren(Argument::type('string'))->willReturn(
            ['contents' => [
                ['is_dir' => true, 'path' => 'dirname'],
            ]],
            ['contents' => [
                ['is_dir' => false, 'path' => 'dirname/file'],
            ]],
            false
        );

        $result = $adapter->listContents('', true);
        $this->assertCount(2, $result);
        $this->assertEquals([], $adapter->listContents('', false));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testRename($adapter, $mock)
    {
        $mock->move(Argument::type('string'), Argument::type('string'))->willReturn(['is_dir' => false, 'path' => 'something']);
        $this->assertTrue($adapter->rename('something', 'something'));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testRenameFail($adapter, $mock)
    {
        $mock->move('/prefix/something', '/prefix/something')->willThrow(new \Dropbox\Exception('Message'));

        $this->assertFalse($adapter->rename('something', 'something'));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testCopy($adapter, $mock)
    {
        $mock->copy(Argument::type('string'), Argument::type('string'))->willReturn(['is_dir' => false, 'path' => 'something']);
        $this->assertTrue($adapter->copy('something', 'something'));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testCopyFail($adapter, $mock)
    {
        $mock->copy(Argument::any(), Argument::any())->willThrow(new \Dropbox\Exception('Message'));

        $this->assertFalse($adapter->copy('something', 'something'));
    }

    /**
     * @dataProvider  dropboxProvider
     */
    public function testGetClient($adapter)
    {
        $this->assertInstanceOf('Dropbox\Client', $adapter->getClient());
    }
}
