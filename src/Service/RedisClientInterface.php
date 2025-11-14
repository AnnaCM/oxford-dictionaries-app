<?php

namespace App\Service;

interface RedisClientInterface
{
    public function exists(string $key): bool;

    public function get(string $key): ?string;

    public function setex(string $key, int $ttl, string $value): void;

    public function zAdd(string $key, array $options, float $value, string $member): void;

    public function zIncrBy(string $key, float $value, string $member): void;

    public function zRange(string $key, int $start, int $end, array $options): array;
}
