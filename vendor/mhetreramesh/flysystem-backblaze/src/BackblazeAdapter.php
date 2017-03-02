<?php

namespace Mhetreramesh\Flysystem;

use ChrisWhite\B2\Client;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Config;

class BackblazeAdapter extends AbstractAdapter {

    use NotSupportingVisibilityTrait;

    protected $client;

    protected $bucketName;

    public function __construct(Client $client, $bucketName)
    {
        $this->client = $client;
        $this->bucketName = $bucketName;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return $this->getClient()->fileExists(['FileName' => $path, 'BucketName' => $this->bucketName]);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        $file = $this->getClient()->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $path,
            'Body' => $contents
        ]);
        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        $file = $this->getClient()->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $path,
            'Body' => $resource
        ]);
        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        echo 'update'; die;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        echo 'updateStream'; die;
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        echo 'read'; die;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        echo ''; die;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        echo 'rename'; die;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        return $this->getClient()->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $newpath,
            'Body' => fopen($path)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        return $this->getClient()->deleteFile(['FileName' => $path, 'BucketName' => $this->bucketName]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($path)
    {
        return $this->getClient()->deleteFile(['FileName' => $path, 'BucketName' => $this->bucketName]);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($path, Config $config)
    {
        return $this->getClient()->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $path
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        echo 'getMetadata'; die;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        echo 'getMimetype'; die;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $file = $this->getClient()->getFile(['FileName' => $path, 'BucketName' => $this->bucketName]);

        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        $file = $this->getClient()->getFile(['FileName' => $path, 'BucketName' => $this->bucketName]);

        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        $fileObjects = $this->getClient()->listFiles([
            'BucketName' => $this->bucketName,
        ]);
        $result = [];
        foreach ($fileObjects as $fileObject) {
            $result[] = $this->getFileInfo($fileObject);
        }
        return $result;
    }

    /**
     * Get file info
     *
     * @param $file
     *
     * @return array
     */

    protected function getFileInfo($file)
    {
        $normalized = [
            'type' => 'file',
            'path' => $file->getName(),
            'timestamp' => substr($file->getUploadTimestamp(), 0, -3),
            'size' => $file->getSize()
        ];

        return $normalized;
    }
}
