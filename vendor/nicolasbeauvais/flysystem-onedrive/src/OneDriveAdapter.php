<?php

namespace NicolasBeauvais\FlysystemOneDrive;

use ArrayObject;
use Microsoft\Graph\Graph;
use League\Flysystem\Config;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;

class OneDriveAdapter extends AbstractAdapter
{
    use NotSupportingVisibilityTrait;

    /** @var \Microsoft\Graph\Graph */
    protected $graph;

    private $usePath;

    public function __construct(Graph $graph, string $prefix = 'root', bool $usePath = true)
    {
        $this->graph = $graph;
        $this->usePath = $usePath;

        $this->setPathPrefix('/drive/'.$prefix.($this->usePath ? ':' : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        return $this->upload($path, $contents);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->upload($path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        return $this->upload($path, $contents);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->upload($path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath): bool
    {
        $endpoint = $this->applyPathPrefix($path);

        $patch = explode('/', $newPath);
        $sliced = implode('/', array_slice($patch, 0, -1));

        try {
            $this->graph->createRequest('PATCH', $endpoint)
                ->attachBody([
                    'name' => end($patch),
                    'parentReference' => [
                        'path' => $this->getPathPrefix().(empty($sliced) ? '' : rtrim($sliced, '/').'/'),
                    ],
                ])
                ->execute();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newPath): bool
    {
        $endpoint = $this->applyPathPrefix($path);

        $patch = explode('/', $newPath);
        $sliced = implode('/', array_slice($patch, 0, -1));

        try {
            $promise = $this->graph->createRequest('POST', $endpoint.($this->usePath ? ':' : '').'/copy')
                ->attachBody([
                    'name' => end($patch),
                    'parentReference' => [
                        'path' => $this->getPathPrefix().(empty($sliced) ? '' : rtrim($sliced, '/').'/'),
                    ],
                ])
                ->executeAsync();
            $promise->wait();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path): bool
    {
        $endpoint = $this->applyPathPrefix($path);

        try {
            $this->graph->createRequest('DELETE', $endpoint)->execute();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname): bool
    {
        return $this->delete($dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        $patch = explode('/', $dirname);
        $sliced = implode('/', array_slice($patch, 0, -1));

        if (empty($sliced) && $this->usePath) {
            $endpoint = str_replace(':/', '', $this->getPathPrefix()).'/children';
        } else {
            $endpoint = $this->applyPathPrefix($sliced).($this->usePath ? ':' : '').'/children';
        }

        try {
            $response = $this->graph->createRequest('POST', $endpoint)
                ->attachBody([
                    'name' => end($patch),
                    'folder' => new ArrayObject(),
                ])->execute();
        } catch (\Exception $e) {
            return false;
        }

        return $this->normalizeResponse($response->getBody(), $dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        if (! $object = $this->readStream($path)) {
            return false;
        }

        $object['contents'] = stream_get_contents($object['stream']);
        fclose($object['stream']);
        unset($object['stream']);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $file = tempnam(sys_get_temp_dir(), 'onedrive');

            $this->graph->createRequest('GET', $path.($this->usePath ? ':' : '').'/content')
                ->download($file);

            $stream = fopen($file, 'r');
        } catch (\Exception $e) {
            return false;
        }

        return compact('stream');
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false): array
    {
        if ($directory === '' && $this->usePath) {
            $endpoint = str_replace(':/', ':/', $this->getPathPrefix()).':/children';
            $endpoint = str_replace(':/:/', '/', $endpoint);
        } else {
            $endpoint = $this->applyPathPrefix($directory).($this->usePath ? ':' : '').'/children';
        }

        try {
            $results = [];
            $response = $this->graph->createRequest('GET', $endpoint)->execute();
            $items = $response->getBody()['value'];

            if (! count($items)) {
                return [];
            }

            foreach ($items as &$item) {
                $results[] = $this->normalizeResponse($item, $this->applyPathPrefix($directory));

                if ($recursive && isset($item['folder'])) {
                    $results = array_merge($results, $this->listContents($directory.'/'.$item['name'], true));
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $response = $this->graph->createRequest('GET', $path)->execute();
        } catch (\Exception $e) {
            return false;
        }

        return $this->normalizeResponse($response->getBody(), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function applyPathPrefix($path): string
    {
        $path = parent::applyPathPrefix($path);

        return '/'.trim($path, '/');
    }

    public function getGraph(): Graph
    {
        return $this->graph;
    }

    /**
     * @param string $path
     * @param resource|string $contents
     *
     * @return array|false file metadata
     */
    protected function upload(string $path, $contents)
    {
        $path = $this->applyPathPrefix($path);

        if (is_resource($contents)) {
            $filesize  = (fstat($contents)['size']);
        } else {
            $filesize = strlen($contents);
        }
        $response = $this->graph->createRequest('POST', $path.($this->usePath ? ':' : '').'/createUploadSession')->execute();
        $uploadUrl = $response->getBody()['uploadUrl'];

        try {
            $contents = $stream = \GuzzleHttp\Psr7\stream_for($contents);

            //$response = $this->graph->createRequest('PUT', $path.($this->usePath ? ':' : '').'/content')
            $response = $this->graph->createRequest('PUT', $uploadUrl)
                ->addHeaders(array("Content-Length" => $filesize, "Content-Range" => 'bytes 0-'.($filesize-1)."/".$filesize))
                ->attachBody(($contents))
                ->execute();
        } catch (\Exception $e) {
            return false;
        }
        
        return $this->normalizeResponse($response->getBody(), $path);
    }

    protected function normalizeResponse(array $response, string $path): array
    {
        $path = trim($this->removePathPrefix($path), '/');

        return [
            'path' => empty($path) ? $response['name'] : $path.'/'.$response['name'],
            'timestamp' => strtotime($response['lastModifiedDateTime']),
            'size' => $response['size'],
            'bytes' => $response['size'],
            'type' => isset($response['file']) ? 'file' : 'dir',
            'mimetype' => isset($response['file']) ? $response['file']['mimeType'] : null,
            'link' => isset($response['webUrl']) ? $response['webUrl'] : null,
        ];
    }
}
