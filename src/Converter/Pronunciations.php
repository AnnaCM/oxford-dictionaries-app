<?php

namespace App\Converter;

use App\Entity\Entries as EntriesEntity;

final class Pronunciations
{
    public static function convert(array $pronunciations, EntriesEntity $entries): EntriesEntity
    {
        foreach ($pronunciations as $pronunciation) {
            $dialect = $pronunciation->dialects[0];
            if ($dialect == 'British English') {
                $dialect = 'UK';
            }
            if ($dialect == 'American English') {
                $dialect = 'US';
            }

            $entries->pronunciations[$dialect] = [];
            if (isset($pronunciation->audioFile)) {
                $entries->pronunciations[$dialect]['audioFile'] = $pronunciation->audioFile;
            }
            if (isset($pronunciation->phoneticSpelling)) {
                $entries->pronunciations[$dialect]['phoneticSpelling'] = $pronunciation->phoneticSpelling;
            }
        }

        return $entries;
    }
}
