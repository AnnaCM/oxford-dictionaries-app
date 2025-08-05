<?php

namespace App\Tests\Service;

use App\Service\CacheStore as CacheStoreService;
use App\Service\RedisClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\Connection\ConnectionException;
use Predis\Connection\NodeConnectionInterface;
use Psr\Log\LoggerInterface;

class CacheStoreTest extends TestCase
{
    private CacheStoreService $cacheService;
    private MockObject $redisClientInterfaceMock;
    private MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->redisClientInterfaceMock = $this->createMock(RedisClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->cacheService = new CacheStoreService($this->redisClientInterfaceMock, $this->loggerMock);
    }

    public function testGetFoundCacheKey()
    {
        $cacheKey = 'key';

        $this->redisClientInterfaceMock
            ->expects($this->once())
            ->method('exists')
            ->with($cacheKey)
            ->willReturn(true);

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Value for key key found in REDIS');

        $this->redisClientInterfaceMock
            ->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn(json_encode(['data' => 'value']));

        $cachedResult = $this->cacheService->get($cacheKey);
        $this->assertEquals((object)['data' => 'value'], $cachedResult);
    }

    public function testGetDoesntFoundCacheKey()
    {
        $cacheKey = 'key';

        $this->redisClientInterfaceMock
            ->expects($this->once())
            ->method('exists')
            ->with($cacheKey)
            ->willReturn(false);

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Value for key key NOT found in REDIS');

        $this->redisClientInterfaceMock
            ->expects($this->never())
            ->method('get');

        $cachedResult = $this->cacheService->get($cacheKey);
        $this->assertNull($cachedResult);
    }

    public function testGetLogsInfoAndHandlesException()
    {
        $cacheKey = 'key';
        $exceptionMessage = 'Connection refused';
        $connectionMock = $this->createMock(NodeConnectionInterface::class);

        $this->redisClientInterfaceMock
            ->expects($this->once())
            ->method('exists')
            ->with($cacheKey)
            ->willThrowException(new ConnectionException($connectionMock, $exceptionMessage));

        $this->redisClientInterfaceMock
            ->expects($this->never())
            ->method('get');

        $this->loggerMock
            ->expects($this->once())
            ->method('warning')
            ->with('Redis unavailable: ' . $exceptionMessage);

        $cachedResult = $this->cacheService->get($cacheKey);
        $this->assertNull($cachedResult);
    }

    public function testSetCacheKey()
    {
        $cacheKey = 'key';
        $result = ['data' => 'value'];

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Setting value for key key in REDIS');

        $this->redisClientInterfaceMock
            ->expects($this->once())
            ->method('setex')
            ->with($cacheKey, 86400, json_encode($result));

        $this->cacheService->set($cacheKey, (object)$result);
    }

    public function testSetLogsInfoAndHandlesException()
    {
        $cacheKey = 'key';
        $result = ['data' => 'value'];
        $exceptionMessage = 'Connection refused';
        $connectionMock = $this->createMock(NodeConnectionInterface::class);

        $this->loggerMock
            ->expects($this->once())
            ->method('warning')
            ->with('Could not cache to Redis: ' . $exceptionMessage);

        $this->redisClientInterfaceMock
            ->expects($this->once())
            ->method('setex')
            ->with($cacheKey, 86400, json_encode($result))
            ->willThrowException(new ConnectionException($connectionMock, $exceptionMessage));

        $this->cacheService->set($cacheKey, (object)$result);
    }
}
