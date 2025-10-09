<?php

namespace App\Service;

use Predis\Connection\ConnectionException;
use Psr\Log\LoggerInterface;

class CacheStore
{
    /** @var \Predis\Client */
    private $redis;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(RedisClientInterface $redis, LoggerInterface $logger)
    {
        $this->redis = $redis;
        $this->logger = $logger;
    }

    public function exists(string $cacheKey): int
    {
        try {
            $keyExists = $this->redis->exists($cacheKey);

            $this->logger->info('Key ' . $cacheKey . ($keyExists ? '' : ' NOT') . ' found in REDIS');

            return $keyExists;
        } catch (ConnectionException $e) {
            $this->logger->warning('Redis unavailable: ' . $e->getMessage());
            // Continue without cache
        }
        return 0;
    }

    public function get(string $cacheKey): ?object
    {
        try {
            if ($this->exists($cacheKey)) {
                return json_decode($this->redis->get($cacheKey));
            }
        } catch (ConnectionException $e) {
            $this->logger->warning('Redis unavailable: ' . $e->getMessage());
            // Continue without cache
        }
        return null;
    }

    public function set(string $cacheKey, object $result)
    {
        try {
            $this->logger->info("Setting value for key {$cacheKey} in REDIS");
            $this->redis->setex($cacheKey, 86400, json_encode($result)); // Cache for 24 hours
        } catch (ConnectionException $e) {
            $this->logger->warning('Could not cache to Redis: ' . $e->getMessage());
        }
    }

    public function zAdd(string $cacheKey, int $value, string $member, array $options = [])
    {
        try {
            $this->logger->info("Adding member {$member} with score {$value} to the sorted set stored at key {$cacheKey}");
            $this->redis->zAdd($cacheKey, $options, $value, $member);
        } catch (ConnectionException $e) {
            $this->logger->warning('Could not cache to Redis: ' . $e->getMessage());
        }
    }

    public function zIncrBy(string $cacheKey, int $value, string $member)
    {
        try {
            $this->logger->info("Incrementing the score of {$member} from the sorted set {$cacheKey} by {$value}");
            $this->redis->zIncrBy($cacheKey, $value, $member);
        } catch (ConnectionException $e) {
            $this->logger->warning('Could not cache to Redis: ' . $e->getMessage());
        }
    }

    public function zRange(string $cacheKey, int $start, int $end, array $options = [])
    {
        try {
            return $this->redis->zRange($cacheKey, $start, $end, $options);
        } catch (ConnectionException $e) {
            $this->logger->warning('Could not cache to Redis: ' . $e->getMessage());
        }
    }
}
