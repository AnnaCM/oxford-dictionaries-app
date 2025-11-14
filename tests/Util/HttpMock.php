<?php

namespace App\Tests\Util;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

trait HttpMock
{
    protected function mockHttpClient(
        string $responseBody = '',
        int $statusCode = 200,
        array $headers = ['Content-Type' => 'application/json'],
    ): MockHttpClient {
        $mockResponse = new MockResponse($responseBody, [
            'http_code' => $statusCode,
            'response_headers' => $headers,
        ]);

        return new MockHttpClient($mockResponse);
    }

    protected function mockHttpSequence(array|callable $responsesOrCallbacks): MockHttpClient
    {
        return new MockHttpClient($responsesOrCallbacks);
    }
}
