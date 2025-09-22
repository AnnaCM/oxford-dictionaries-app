<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

class LoadWordsToRedisCommand extends Command
{
    protected static $defaultName = 'app:load-words';

    private ClientInterface $redis;

    public function __construct(ClientInterface $redis)
    {
        parent::__construct();
        $this->redis = $redis;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $words = file(__DIR__ . '/../../data/words.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($words as $word) {
            $word = strtolower($word);
            $this->redis->zAdd('dictionary_words', 0, $word);
            $output->writeln("Word {$word} loaded into Redis.");
        }

        $output->writeln('✅ Word loading completed.');
        return Command::SUCCESS;
    }
}
