<?php

namespace App\Converter;

use App\Entity\Definitions as DefinitionsEntity;

final class Definitions implements Convertable
{
    public static function convert(object $responseData): DefinitionsEntity
    {
        $definitions = new DefinitionsEntity();
        $definitions->text = $responseData->query;

        $results = $responseData->results;

        $definitions->pronunciations = [];
        $definitions->senses = [];

        if (isset($results[0]->pronunciations)) {
            $definitions = Pronunciations::convert($results[0]->pronunciations, $definitions);
        }

        foreach ($results as $result) {
            $lexicalEntries = $result->lexicalEntries;
            foreach ($lexicalEntries as $lexicalEntry) {
                $entries = $lexicalEntry->entries;
                foreach ($entries as $entry) {
                    $senses = $entry->senses;
                    foreach ($senses as $sense) {
                        $definitionsSenses = [];

                        if (isset($sense->definitions)) {
                            $definitionsSenses['definitions'] = $sense->definitions;
                        }

                        if (isset($sense->examples)) {
                            $definitionsSenses['examples'] = $sense->examples;
                        }

                        $definitions->senses[$lexicalEntry->lexicalCategory->id][] = $definitionsSenses;
                    }

                    if (!$definitions->pronunciations) {
                        if (isset($entry->pronunciations)) {
                            $definitions = Pronunciations::convert($entry->pronunciations, $definitions);
                        }
                    }
                }
            }
        }

        return $definitions;
    }
}
