<?php

namespace App\Service;

use Predis\Connection\ConnectionException;
use Psr\Log\LoggerInterface;

class CacheStore
{
    public function __construct(
        private RedisClientInterface $redis,
        private LoggerInterface $logger
    ) {}

    public function exists(string $cacheKey): bool
    {
        try {
            $exists = $this->redis->exists($cacheKey);
            $this->logger->info(sprintf('Key "%s"%s found in Redis', $cacheKey, $exists ? '' : ' NOT'));
            return $exists;
        } catch (ConnectionException $e) {
            $this->logger->warning('Redis unavailable: ' . $e->getMessage());
            return false;
        }
    }

    public function get(string $cacheKey): ?object
    {
        try {
            if ($this->exists($cacheKey)) {
                return json_decode($this->redis->get($cacheKey));
            }
        } catch (ConnectionException $e) {
            $this->logger->warning('Redis unavailable: ' . $e->getMessage());
        }
        return null;
    }

    public function set(string $cacheKey, object $result): void
    {
        try {
            $this->logger->info(sprintf('Setting value for key "%s" in Redis', $cacheKey));
            $this->redis->setex($cacheKey, 86400, json_encode($result, JSON_UNESCAPED_UNICODE));
        } catch (ConnectionException $e) {
            $this->logger->warning('Could not cache to Redis: ' . $e->getMessage());
        }
    }

    public function zAdd(string $cacheKey, int $value, string $member, array $options = []): void
    {
        try {
            $this->logger->info("Adding member {$member} with score {$value} to sorted set {$cacheKey}");
            $this->redis->zAdd($cacheKey, $options, $value, $member);
        } catch (ConnectionException $e) {
            $this->logger->warning('Could not write to Redis: ' . $e->getMessage());
        }
    }

    public function zIncrBy(string $cacheKey, int $value, string $member): void
    {
        try {
            $this->logger->info("Incrementing score of {$member} in {$cacheKey} by {$value}");
            $this->redis->zIncrBy($cacheKey, $value, $member);
        } catch (ConnectionException $e) {
            $this->logger->warning('Could not write to Redis: ' . $e->getMessage());
        }
    }

    public function zRange(string $cacheKey, int $start, int $end, array $options = []): array
    {
        try {
            return $this->redis->zRange($cacheKey, $start, $end, $options);
        } catch (ConnectionException $e) {
            $this->logger->warning('Could not read from Redis: ' . $e->getMessage());
            return [];
        }
    }
}
