<?php

namespace App\Tests\Service\Util;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

trait HttpMock
{
    protected function mockHttpClient(string $endpoint, string $responseBody, int $httpStatusCode): MockHttpClient
    {
        $response = new MockResponse($responseBody, ['http_code' => $httpStatusCode]);
        return new MockHttpClient($response, $endpoint);
    }
}
