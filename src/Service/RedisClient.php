<?php

namespace App\Service;

use SymfonyBundles\RedisBundle\Redis\ClientInterface as SymfonyRedisClient;

class RedisClient implements RedisClientInterface
{
    public function __construct(private SymfonyRedisClient $client) {}

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

    public function zAdd(string $key, array $options, float $value, string $member): void
    {
        $this->client->zAdd($key, ...$options, ...[$value, $member]);
    }

    public function zIncrBy(string $key, float $value, string $member): void
    {
        $this->client->zIncrBy($key, $value, $member);
    }

    public function zRange(string $key, int $start, int $end, array $options): array
    {
        return $this->client->zRange($key, $start, $end, ...$options);
    }
}
