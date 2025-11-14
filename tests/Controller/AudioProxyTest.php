<?php

namespace App\Tests\Controller;

use App\Controller\AudioProxy as AudioProxyController;
use App\Tests\Util\HttpMock;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AudioProxyTest extends Base
{
    use HttpMock;

    public function testStreamReturnsAudioStreamOnSuccess()
    {
        $mockHttp = $this->mockHttpSequence([
            new MockResponse('', ['http_code' => 200]),
            new MockResponse('', [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'audio/mpeg'],
            ]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockHttp);

        $this->client->request('GET', '/audio-proxy/test.mp3');

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'audio/mpeg'));
    }

    public function testStreamOutputsAudioInChunks()
    {
        $mockHttp = $this->mockHttpSequence([
            new MockResponse('', ['http_code' => 200]),
            new MockResponse('chunk1chunk2chunk3', [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'audio/mpeg'],
            ]),
        ]);
        $controller = new AudioProxyController($mockHttp);

        $response = $controller->stream('test-audio.mp3');
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'audio/mpeg'));

        // Capture streamed output
        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('chunk1', $output);
        $this->assertStringContainsString('chunk2', $output);
        $this->assertStringContainsString('chunk3', $output);
    }

    public function testStreamReturns404WhenHeadFails()
    {
        $mockHttp = $this->mockHttpSequence([new MockResponse('Not Found', ['http_code' => 404])]);
        static::getContainer()->set(HttpClientInterface::class, $mockHttp);

        $this->client->request('GET', '/audio-proxy/missing.mp3');

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Audio unavailable', $response->getContent());
    }

    public function testStreamHandlesNetworkTimeoutGracefully()
    {
        $mockHttp = $this->mockHttpSequence(function () {
            throw new TimeoutException('Network timeout');
        });
        static::getContainer()->set(HttpClientInterface::class, $mockHttp);

        $this->client->request('GET', '/audio-proxy/timeout.mp3');

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Audio unavailable', $response->getContent());
    }

    public function testStreamHandlesServerErrorGracefully()
    {
        $mockHttp = $this->mockHttpClient('Internal Server Error', 500);
        static::getContainer()->set(HttpClientInterface::class, $mockHttp);

        $this->client->request('GET', '/audio-proxy/server-error.mp3');

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Audio unavailable.', $response->getContent());
    }
}
