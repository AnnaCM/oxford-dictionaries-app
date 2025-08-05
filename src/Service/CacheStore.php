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

    public function get(string $cacheKey): ?object
    {
        try {
            if ($this->redis->exists($cacheKey)) {
                $this->logger->info("Value for key {$cacheKey} found in REDIS");
                return json_decode($this->redis->get($cacheKey));
            }

            $this->logger->info("Value for key {$cacheKey} NOT found in REDIS");
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
}
