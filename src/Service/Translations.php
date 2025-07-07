<?php

namespace App\Service;

use App\Converter\Translations as TranslationsConverter;
use App\Exception\ValidationError;

class Translations extends Dictionary
{
    public function getTranslations(string $sourceLang, string $targetLang, string $word)
    {
        $this->validateLanguageCode($sourceLang, $targetLang);
        $responseData = $this->get("/translations/{$sourceLang}/{$targetLang}/{$word}");

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
