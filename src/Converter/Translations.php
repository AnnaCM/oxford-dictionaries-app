<?php

namespace App\Converter;

use App\Entity\Translations as TranslationsEntity;

final class Translations implements Convertable
{
    public static function convert(object $responseData): TranslationsEntity
    {
        $translations = new TranslationsEntity();
        $translations->text = $responseData->id;

        $results = $responseData->results;

        $translations->pronunciations = [];
        $translations->senses = [];

        if (isset($results[0]->pronunciations)) {
            $translations = Pronunciations::convert($results[0]->pronunciations, $translations);
        }

        foreach ($results as $result) {
            $lexicalEntries = $result->lexicalEntries;
            foreach ($lexicalEntries as $lexicalEntry) {
                $entries = $lexicalEntry->entries;
                foreach ($entries as $entry) {
                    $senses = $entry->senses;
                    foreach ($senses as $sense) {
                        $translationsSenses = [];

                        if (isset($sense->translations)) {
                            $translationsSenses['translations'] = $sense->translations;
                        }

                        if (isset($sense->notes)) {
                            $translationsSenses['notes'] = $sense->notes;
                        }

                        if (isset($sense->examples)) {
                            $translationsSenses['examples'] = $sense->examples;
                        }

                        if (isset($sense->definitions)) {
                            $translationsSenses['definitions'] = $sense->definitions;
                        }

                        $translations->senses[$lexicalEntry->lexicalCategory->id][] = $translationsSenses;
                    }

                    if (!$translations->pronunciations) {
                        if (isset($entry->pronunciations)) {
                            $translations = Pronunciations::convert($entry->pronunciations, $translations);
                        }
                    }
                }
            }
        }

        return $translations;
    }
}
