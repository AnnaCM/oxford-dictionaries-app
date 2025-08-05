<?php

namespace App\Service;

use SymfonyBundles\RedisBundle\Redis\ClientInterface as SymfonyRedisClient;

class RedisClient implements RedisClientInterface
{
    private SymfonyRedisClient $client;

    public function __construct(SymfonyRedisClient $client)
    {
        $this->client = $client;
    }

    public function exists(string $key): bool
    {
        return $this->client->exists($key);
    }

    public function get(string $key): ?string
    {
        return $this->client->get($key);
    }

    public function setex(string $key, int $ttl, string $value): void
    {
        $this->client->setex($key, $ttl, $value);
    }
}
