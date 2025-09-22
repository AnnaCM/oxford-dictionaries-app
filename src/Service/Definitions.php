<?php

namespace App\Service;

use App\Converter\Definitions as DefinitionsConverter;
use App\Entity\Definitions as DefinitionsEntity;
use App\Exception\ValidationError;
use App\Service\CacheStore as CacheStoreService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Definitions extends Dictionary
{
    private CacheStoreService $cache;

    public function __construct(
        CacheStoreService $cache,
        HttpClientInterface $client,
        string $serverHost,
        string $appId,
        string $appKey
    ) {
        $this->cache = $cache;
        parent::__construct($client, $serverHost, $appId, $appKey);
    }

    public function getDefinitions(string $sourceLang, string $word): DefinitionsEntity
    {
        $this->validateLanguageCode($sourceLang);

        $cacheKey = implode('_', [__METHOD__, $word, $sourceLang]);

        $cachedResult = $this->cache->get($cacheKey);
        if ($cachedResult) {
            return DefinitionsConverter::convert($cachedResult);
        }

        $responseData = $this->get("/words/{$sourceLang}", ['query' => ['q' => $word]]);

        $this->cache->set($cacheKey, $responseData);
        $this->cache->zIncrBy('dictionary_words', 1, strtolower($word));

         return DefinitionsConverter::convert($responseData);
    }

    private function validateLanguageCode(string $sourceLang)
    {
        if (!in_array($sourceLang, array_keys($this::ALLOWED_DEFINITIONS_SOURCE_LANGS))) {
            throw new ValidationError("Invalid code for source language: {$sourceLang}");
        }
    }
}
