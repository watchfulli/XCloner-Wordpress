<?php

require_once __DIR__.'/../lib/Dropbox/strict.php';

use \Dropbox as dbx;

PHPUnit_Framework_Error_Notice::$enabled = true;
PHPUnit_Framework_Error_Warning::$enabled = true;

class ClientTest extends PHPUnit_Framework_TestCase
{
    private $client;

    const E_ACCENT = "\xc3\xa9";  # UTF-8 sequence for "e with accute accent"

    function __construct()
    {
        $authFile = __DIR__."/test.auth";

        try {
            list($accessToken, $host) = dbx\AuthInfo::loadFromJsonFile($authFile);
        } catch (dbx\AuthInfoLoadException $ex) {
            echo "Error loading auth-info: ".$ex->getMessage()."\n";
            die;
        }

        $userLocale = "en";
        $this->client = new dbx\Client($accessToken, "sdk-tests", $userLocale, $host);
    }

    private $testFolder;

    private function p($path = null)
    {
        if ($path === null) return $this->testFolder;
        return "{$this->testFolder}/$path";
    }

    protected function setUp()
    {
        // Create a new folder for the tests to work with.
        $timestamp = \date('Y-M-d H.i.s', \time());
        $basePath = "/PHP SDK Tests/$timestamp";

        $tryPath = $basePath;
        $result = $this->client->createFolder($basePath);
        $i = 2;
        while ($result == null) {
            $tryPath = "$basePath ($i)";
            $i++;
            if ($i >= 100) throw new Exception("Unable to create folder \"$basePath\"");
            $result = $this->client->createFolder($basePath);
        }

        $this->testFolder = $tryPath;
    }

    function tearDown()
    {
        @unlink("test-dest.txt");
        @unlink("test-source.txt");

        $this->client->delete($this->testFolder);
    }

    function writeTempFile($size)
    {
        $fd = tmpfile();

        $chars = "\nabcdefghijklmnopqrstuvwxyz0123456789";
        for ($i = 0; $i < $size; $i++) {
            fwrite($fd, $chars[rand() % strlen($chars)]);
        }

        fseek($fd, 0);

        return $fd;
    }

    private function addFile($path, $size, $writeMode = null)
    {
        if ($writeMode === null) $writeMode = dbx\WriteMode::add();

        $fd = $this->writeTempFile($size);
        $result = $this->client->uploadFile($path, $writeMode, $fd, $size);
        fclose($fd);
        $this->assertEquals($size, $result['bytes']);

        return $result;
    }

    private function fetchUrl($url)
    {
        //sadly, https doesn't work out of the box on windows for functions
        //like file_get_contents, so let's make this easy for devs

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $ret = curl_exec($ch);

        curl_close($ch);

        return $ret;
    }

    function testUploadAndDownload()
    {
        $localPathSource = "test-source.txt";
        $localPathDest = "test-dest.txt";
        $contents = "A simple test file";
        file_put_contents($localPathSource, $contents);

        $remotePath = $this->p("test-fil".self::E_ACCENT.".txt");

        $fp = fopen($localPathSource, "rb");
        $up = $this->client->uploadFile($remotePath, dbx\WriteMode::add(), $fp);
        fclose($fp);
        $this->assertEquals($up["path"], $remotePath);

        $fd = fopen($localPathDest, "wb");
        $down = $this->client->getFile($remotePath, $fd);
        fclose($fd);

        $this->assertEquals($up['bytes'], $down['bytes']);
        $this->assertEquals($up['bytes'], filesize($localPathSource));
        $this->assertEquals(filesize($localPathDest), filesize($localPathSource));
    }

    function testMetadata()
    {
        $this->addFile($this->p("a.txt"), 100);

        $md = $this->client->getMetadataWithChildren($this->p());
        $this->assertEquals(1, count($md['contents']));

        // folder metadata should be the same
        $hash = $md['hash'];
        list($changed, $new_md) = $this->client->getMetadataWithChildrenIfChanged($this->p(), $hash);
        $this->assertFalse($changed);
        $this->assertEquals($new_md, null);

        $this->addFile($this->p("b.txt"), 100);

        // folder metadata should be different (since we added another file)
        $hash = $md['hash'];
        list($changed, $new_md) = $this->client->getMetadataWithChildrenIfChanged($this->p(), $hash);
        $this->assertTrue($changed);
        $this->assertEquals(2, count($new_md['contents']));
    }

    function testDelta()
    {
        // eat up all the deltas to the point where we should expect exactly
        // one after we add a new file
        $result = $this->client->getDelta();
        $this->assertTrue($result['reset']);
        $start = $result['cursor'];

        do {
            $result = $this->client->getDelta($start);
            $start = $result['cursor'];
        } while ($result['has_more']);

        $path = $this->p("make a delta.txt");

        $this->addFile($path, 100);
        $result = $this->client->getDelta($start);
        $this->assertEquals(1, count($result['entries']));
        $this->assertEquals($path, $result['entries'][0][1]["path"]);
    }

    function testDeltaPathPrefix()
    {
        // eat up all the deltas to the point where we should expect exactly
        // one after we add a new file
        $pathPrefix = $this->p("folder");
        $result = $this->client->getDelta(null, $pathPrefix);
        $this->assertTrue($result['reset']);
        $this->assertEquals($result['entries'], array());
        $cursor = $result['cursor'];

        $this->addFile($this->p("stuff.txt"), 1);
        $this->client->createFolder($this->p("folder"));
        $this->client->createFolder($this->p("folder2"));
        $this->addFile($this->p("folder/a.txt"), 2);
        $this->addFile($this->p("folder/b.txt"), 3);
        $this->addFile($this->p("folder2/a.txt"), 4);
        $this->addFile($this->p("folder.txt"), 5);

        $allEntries = array();

        do {
            $result = $this->client->getDelta($cursor, $pathPrefix);
            $cursor = $result['cursor'];
            foreach ($result['entries'] as $entry) {
                $allEntries[$entry[1]['path']] = $entry[1]['bytes'];
            }
        } while ($result['has_more']);

        $this->assertEquals($allEntries, array(
            $this->p("folder") => 0,
            $this->p("folder/a.txt") => 2,
            $this->p("folder/b.txt") => 3));
    }

    function testRevisions()
    {
        $path = $this->p("revisions.txt");
        $this->addFile($path, 100, dbx\WriteMode::force());
        $this->addFile($path, 200, dbx\WriteMode::force());
        $this->addFile($path, 300, dbx\WriteMode::force());

        $revs = $this->client->getRevisions($path);
        $this->assertEquals(count($revs), 3);

        $revs = $this->client->getRevisions($path, 2);
        $this->assertEquals(count($revs), 2);
        $this->assertEquals(300, $revs[0]['bytes']);
        $this->assertEquals(200, $revs[1]['bytes']);
    }

    function testRestore()
    {
        $path = $this->p("revisions.txt");
        $resultA = $this->addFile($path, 100);
        $this->addFile($path, 200);

        $result = $this->client->restoreFile($path, $resultA['rev']);
        $this->assertEquals(100, $result['bytes']);

        $final = $this->client->getMetadata($path);
        $this->assertEquals(100, $final['bytes']);
    }

    function testSearch()
    {
        $this->addFile($this->p("search - a.txt"), 100);
        $this->client->createFolder($this->p("sub"));
        $this->addFile($this->p("sub/search - b.txt"), 200);
        $this->addFile($this->p("search - c.txt"), 200);
        $this->client->delete($this->p("search - c.txt"));

        $result = $this->client->searchFileNames($this->p(), "search");
        $this->assertEquals(2, count($result));

        $result = $this->client->searchFileNames($this->p(), "search", 1);
        $this->assertEquals(1, count($result));

        $result = $this->client->searchFileNames($this->p("sub"), "search");
        $this->assertEquals(1, count($result));

        $result = $this->client->searchFileNames($this->p(), "search", null, true);
        $this->assertEquals(3, count($result));
    }

    function testShares()
    {
        $contents = "A shared text file";
        $remotePath = $this->p("share-me.txt");
        $this->client->uploadFileFromString($remotePath, dbx\WriteMode::add(), $contents);

        $url = $this->client->createShareableLink($remotePath);
        $fetchedStr = $this->fetchUrl($url);
        assert(strlen($fetchedStr) > 5 * strlen($contents)); //should get a big page back
    }

    function testMedia()
    {
        $contents = "A media text file";

        $remotePath = $this->p("media-me.txt");
        $this->client->uploadFileFromString($remotePath, dbx\WriteMode::add(), $contents);

        list($url, $expires) = $this->client->createTemporaryDirectLink($remotePath);
        $fetchedStr = $this->fetchUrl($url);

        $this->assertEquals($contents, $fetchedStr);
    }

    function testCopyRef()
    {
        $source = $this->p("copy-ref me.txt");
        $dest = $this->p("ok - copied ref.txt");
        $size = 1024;

        $this->addFile($source, $size);
        $ref = $this->client->createCopyRef($source);

        $result = $this->client->copyFromCopyRef($ref, $dest);
        $this->assertEquals($size, $result['bytes']);

        $result = $this->client->getMetadataWithChildren($this->p());
        $this->assertEquals(2, count($result['contents']));
    }

    function testThumbnail()
    {
        $remotePath = $this->p("image.jpg");
        $localPath = __DIR__."/upload.jpg";
        $fp = fopen($localPath, "rb");
        $this->client->uploadFile($remotePath, dbx\WriteMode::add(), $fp);
        fclose($fp);

        list($md1, $data1) = $this->client->getThumbnail($remotePath, "jpeg", "xs");
        $this->assertTrue(self::isJpeg($data1));

        list($md2, $data2) = $this->client->getThumbnail($remotePath, "jpeg", "s");
        $this->assertTrue(self::isJpeg($data1));
        $this->assertGreaterThan(strlen($data1), strlen($data2));

        list($md3, $data3) = $this->client->getThumbnail($remotePath, "png", "s");
        $this->assertTrue(self::isPng($data3));
    }

    static function isJpeg($data)
    {
        $first_two = substr($data, 0, 2);
        $last_two = substr($data, -2);
        return ($first_two === "\xFF\xD8") && ($last_two === "\xFF\xD9");
    }

    static function isPng($data)
    {
        return substr($data, 0, 8) === "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a";
    }

    function testChunkedUpload()
    {
        $fd = $this->writeTempFile(1200);
        $contents = stream_get_contents($fd);
        fseek($fd, 0);

        $remotePath = $this->p("chunked-upload.txt");
        $this->client->uploadFileChunked($remotePath, dbx\WriteMode::add(), $fd, null, 512);

        $fd = tmpfile();
        $this->client->getFile($remotePath, $fd);
        fseek($fd, 0);
        $fetched = stream_get_contents($fd);
        fclose($fd);

        $this->assertEquals($contents, $fetched);
    }

    function testChunkedUploadWithSize()
    {
        $fd = $this->writeTempFile(1200);
        $contents = stream_get_contents($fd);
        fseek($fd, 0);

        $remotePath = $this->p("chunked-upload.txt");
        $this->client->uploadFileChunked($remotePath, dbx\WriteMode::add(), $fd, 1200, 512);

        $fd = tmpfile();
        $this->client->getFile($remotePath, $fd);
        fseek($fd, 0);
        $fetched = stream_get_contents($fd);
        fclose($fd);

        $this->assertEquals($contents, $fetched);
    }

    function testChunkedUploadWithFailures()
    {
        $client = new ClientForChunkedUploadWithFailures(
            $this->client->getAccessToken(),
            $this->client->getClientIdentifier(),
            $this->client->getUserLocale(),
            $this->client->getHost());

        $fd = $this->writeTempFile(10);
        $contents = stream_get_contents($fd);

        $remotePath = $this->p("chunked-upload.txt");

        // Simulate losing a request or response.
        foreach (array('lose-request', 'lose-response') as $secondInstruction) {
            // Upload.
            $client->callCounter = 0;
            $client->instructions = array('ok', $secondInstruction);
            fseek($fd, 0);
            $md = $client->uploadFileChunked($remotePath, dbx\WriteMode::add(), $fd, 10, 4);
            $this->assertEquals($client->callCounter, 4);

            // Download and verify.
            $outFd = tmpfile();
            $this->client->getFile($md['path'], $outFd);
            fseek($outFd, 0);
            $fetched = stream_get_contents($outFd);
            $this->assertEquals($fetched, $contents);
        }

        // Failing four times in a row should cause us to give up.
        $client->callCounter = 0;
        $client->instructions =
            array('ok', 'lose-request', 'lose-request', 'lose-request', 'lose-response');
        fseek($fd, 0);
        try {
            $client->uploadFileChunked($remotePath, dbx\WriteMode::add(), $fd, 10, 4);
            assert(false);  // Should never get here.
        }
        catch (dbx\Exception_NetworkIO $ex) {
            // We want thie exception to happen.
            $this->assertEquals($ex->getMessage(), "simulate lose-response");
            $this->assertEquals($client->callCounter, 5);
        }
    }

    // --------------- Test File Operations -------------------
    function testCopy()
    {
        $source = $this->p("copy m".self::E_ACCENT.".txt");
        $dest = $this->p("ok - copi".self::E_ACCENT."d.txt");
        $size = 1024;

        $this->addFile($source, $size);
        $result = $this->client->copy($source, $dest);
        $this->assertEquals($size, $result['bytes']);
        $this->assertEquals($dest, $result['path']);

        $result = $this->client->getMetadataWithChildren($this->p());
        $this->assertEquals(2, count($result['contents']));
    }

    function testCreateFolder()
    {
        $result = $this->client->getMetadataWithChildren($this->p());
        $this->assertEquals(0, count($result['contents']));

        $this->client->createFolder($this->p("a"));

        $result = $this->client->getMetadataWithChildren($this->p());
        $this->assertEquals(1, count($result['contents']));

        $result = $this->client->getMetadata($this->p("a"));
        $this->assertTrue($result['is_dir']);
    }

    function testDelete()
    {
        $path = $this->p("delete me.txt");
        $size = 1024;

        $this->addFile($path, $size);
        $this->client->delete($path);

        $result = $this->client->getMetadataWithChildren($this->p());
        $this->assertEquals(0, count($result['contents']));
    }

    function testMove()
    {
        $source = $this->p("move me.txt");
        $dest = $this->p("ok - moved.txt");
        $size = 1024;

        $this->addFile($source, $size);
        $result = $this->client->getMetadataWithChildren($this->p());
        $this->assertEquals(1, count($result['contents']));

        $result = $this->client->move($source, $dest);
        $this->assertEquals($size, $result['bytes']);

        $result = $this->client->getMetadataWithChildren($this->p());
        $this->assertEquals(1, count($result['contents']));
    }
}

class ClientForChunkedUploadWithFailures extends dbx\Client
{
    public $callCounter = 0;
    public $instructions = array();
    public $callsToAllow = 0;
    public $callsToFail = 0;

    protected function _chunkedUpload($params, $data)
    {
        $this->callCounter += 1;

        if (count($this->instructions) > 0) {
            $instruction = array_shift($this->instructions);
        } else {
            $instruction = 'ok';
        }

        if ($instruction === 'lose-request') {
            throw new dbx\Exception_NetworkIO("simulate lose-request");
        }
        else if ($instruction === 'lose-response') {
            parent::_chunkedUpload($params, $data);
            throw new dbx\Exception_NetworkIO("simulate lose-response");
        }
        else if ($instruction === 'ok') {
            return parent::_chunkedUpload($params, $data);
        }
        else {
            throw new \InvalidArgumentException("invalid instruction: \"$instruction\"");
        }
    }
}
