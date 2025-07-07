<?php

namespace App\Service;

use App\Converter\Definitions as DefinitionsConverter;
use App\Entity\Definitions as DefinitionsEntity;
use App\Exception\ValidationError;

class Definitions extends Dictionary
{
    public function getDefinitions(string $sourceLang, string $word): DefinitionsEntity
    {
        $this->validateLanguageCode($sourceLang);
        $responseData = $this->get("/words/{$sourceLang}", ['query' => ['q' => $word]]);

         return DefinitionsConverter::convert($responseData);
    }

    private function validateLanguageCode(string $sourceLang)
    {
        if (!in_array($sourceLang, array_keys($this::ALLOWED_DEFINITIONS_SOURCE_LANGS))) {
            throw new ValidationError("Invalid code for source language: {$sourceLang}");
        }
    }
}
