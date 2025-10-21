<?php

namespace App\Command;

use App\Service\CacheStore as CacheStoreService;
use App\Service\Dictionary as DictionaryService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadWordsToRedisCommand extends Command
{
    protected static $defaultName = 'app:load-dictionary-words';

    public function __construct(
        private CacheStoreService $cache,
        private DictionaryService $service
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = __DIR__ . '/../../data';
        $entries = scandir($directory);

        $validSourceLanguages = array_unique(
            array_map(function (string $sourceLang) {
                    return explode('-', $sourceLang)[0];
                },
                array_merge(
                    array_keys($this->service::ALLOWED_DEFINITIONS_SOURCE_LANGS),
                    array_keys($this->service::ALLOWED_TRANSLATIONS_SOURCE_LANGS)
                )
            )
        );

        foreach ($entries as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                $filenameParts = explode('_', $entry);
                if ((count($filenameParts) == 1) || !in_array($filenameParts[0], $validSourceLanguages)) {
                    echo "Skipping {$entry} as filename or source language code is not valid\n";
                    continue;
                }

                $sourceLang = $filenameParts[0];

                echo "Processing {$entry}\n";

                $path = $directory . '/' . $entry;
                $words = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($words as $word) {
                    $word = mb_strtolower($word, 'UTF-8');
                    $word = mb_convert_encoding($word, 'UTF-8', 'auto');
                    // Only add new elements. Don't update already existing elements.
                    $this->cache->zAdd("{$sourceLang}_dictionary_words", 0, $word, ['NX']);
                }

                $output->writeln("✅ Word loading for source language {$sourceLang} completed.");
            }
        }

        return Command::SUCCESS;
    }
}
