<?php

namespace App\Converter;

use App\Entity\Entries as EntriesEntity;

final class Pronunciations
{
    public static function convert(array $pronunciations, EntriesEntity $entries): EntriesEntity
    {
        foreach ($pronunciations as $pronunciation) {
            $dialect = '';
            if (isset($pronunciation->dialects)) {
                $dialect = $pronunciation->dialects[0];
                if ('British English' == $dialect) {
                    $dialect = 'UK';
                }
                if ('American English' == $dialect) {
                    $dialect = 'US';
                }

                $entries->pronunciations[$dialect] = [];
                if (isset($pronunciation->audioFile)) {
                    $entries->pronunciations[$dialect]['audioFile'] = $pronunciation->audioFile;
                }
                if (isset($pronunciation->phoneticSpelling)) {
                    $entries->pronunciations[$dialect]['phoneticSpelling'] = $pronunciation->phoneticSpelling;
                }
            } else {
                if (isset($pronunciation->audioFile) && isset($pronunciation->phoneticSpelling)) {
                    $entries->pronunciations[] = [
                        'audioFile' => $pronunciation->audioFile,
                        'phoneticSpelling' => $pronunciation->phoneticSpelling,
                    ];
                } elseif (isset($pronunciation->audioFile)) {
                    $entries->pronunciations[]['audioFile'] = $pronunciation->audioFile;
                } elseif (isset($pronunciation->phoneticSpelling)) {
                    $entries->pronunciations[]['phoneticSpelling'] = $pronunciation->phoneticSpelling;
                }
            }
        }

        return $entries;
    }
}
