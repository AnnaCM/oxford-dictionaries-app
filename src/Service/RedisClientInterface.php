<?php

namespace App\Service;

interface RedisClientInterface
{
    public function exists(string $key): bool;
    public function get(string $key): ?string;
    public function setex(string $key, int $ttl, string $value): void;
}
