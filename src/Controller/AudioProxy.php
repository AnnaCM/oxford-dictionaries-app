<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AudioProxy
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    #[Route('/audio-proxy/{filename<.+>}', name: 'audio_proxy', methods: ['GET'])]
    public function stream(string $filename): Response
    {
        $url = "https://audio.oxforddictionaries.com/en/mp3/{$filename}";

        try {
            $headResponse = $this->client->request('HEAD', $url);
            if (200 !== $headResponse->getStatusCode()) {
                return new Response(
                    'Audio unavailable.',
                    Response::HTTP_NOT_FOUND,
                    ['Content-Type' => 'text/plain']
                );
            }

            $response = $this->client->request('GET', $url);

            return new StreamedResponse(function () use ($response) {
                echo $response->getContent();
                flush();
            }, 200, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'max-age=3600',
            ]);
        } catch (\Throwable $e) {
            return new Response(
                'Audio unavailable.',
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'text/plain']
            );
        }
    }
}
