<?php

namespace ChrisWhite\B2\Tests;

use ChrisWhite\B2\Http\Client as HttpClient;

trait TestHelper
{
    protected function buildGuzzleFromResponses(array $responses, $history = null)
    {
        $mock = new \GuzzleHttp\Handler\MockHandler($responses);
        $handler = new \GuzzleHttp\HandlerStack($mock);

        if ($history) {
            $handler->push($history);
        }

        return new HttpClient(['handler' => $handler]);
    }

    protected function buildResponseFromStub($statusCode, array $headers = [], $responseFile)
    {
        $response = file_get_contents(dirname(__FILE__).'/responses/'.$responseFile);

        return new \GuzzleHttp\Psr7\Response($statusCode, $headers, $response);
    }
}
