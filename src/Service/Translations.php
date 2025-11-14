<?php

namespace App\Service;

use App\Converter\Translations as TranslationsConverter;
use App\Exception\ValidationError;
use App\Service\CacheStore as CacheStoreService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Translations extends Dictionary
{
    public function __construct(
        private CacheStoreService $cache,
        HttpClientInterface $client,
        string $serverHost,
        string $appId,
        string $appKey,
    ) {
        parent::__construct($client, $serverHost, $appId, $appKey);
    }

    public function getTranslations(string $sourceLang, string $targetLang, string $word)
    {
        $this->validateLanguageCode($sourceLang, $targetLang);

        $cacheKey = implode('_', [__METHOD__, $word, $sourceLang, $targetLang]);

        $cachedResult = $this->cache->get($cacheKey);
        if ($cachedResult) {
            return TranslationsConverter::convert($cachedResult);
        }

        $responseData = $this->get("/translations/{$sourceLang}/{$targetLang}/{$word}");

        $this->cache->set($cacheKey, $responseData);
        $this->cache->zIncrBy("{$sourceLang}_dictionary_words", 1, mb_strtolower($word));

        return TranslationsConverter::convert($responseData);
    }

    private function validateLanguageCode(string $sourceLang, string $targetLang)
    {
        if (!in_array($sourceLang, array_keys($this::ALLOWED_TRANSLATIONS_SOURCE_LANGS))) {
            throw new ValidationError("Invalid code for source language: {$sourceLang}");
        }

        if (!in_array($targetLang, array_keys($this::ALLOWED_TRANSLATIONS_TARGET_LANGS))) {
            throw new ValidationError("Invalid code for target language: {$targetLang}");
        }
    }
}
