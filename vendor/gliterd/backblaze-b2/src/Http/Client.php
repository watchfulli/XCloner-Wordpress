<?php

namespace BackblazeB2\Http;

use BackblazeB2\ErrorHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Client wrapper around Guzzle.
 */
class Client extends GuzzleClient
{
    /**
     * Sends a response to the B2 API, automatically handling decoding JSON and errors.
     *
     * @param string $method
     * @param null   $uri
     * @param array  $options
     * @param bool   $asJson
     *
     * @throws GuzzleException
     *
     * @return mixed|ResponseInterface|string
     */
    public function request($method, $uri = null, array $options = [], $asJson = true)
    {
        $response = parent::request($method, $uri, $options);

        if ($response->getStatusCode() !== 200) {
            ErrorHandler::handleErrorResponse($response);
        }

        if ($asJson) {
            return json_decode($response->getBody(), true);
        }

        return $response->getBody()->getContents();
    }
}
