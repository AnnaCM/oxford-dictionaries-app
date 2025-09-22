<?php

namespace App\Tests\Controller;

use App\Service\CacheStore as CacheService;
use PHPUnit\Framework\MockObject\MockObject;

class AutocompleteTest extends Base
{
    private MockObject $cacheServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSuggestReturnsEmptyResult()
    {
        $this->cacheServiceMock->expects($this->never())->method('zRange');
        static::getContainer()->set(CacheService::class, $this->cacheServiceMock);

        $this->client->request('GET', '/autocomplete?q=a');
        $this->assertSame('[]', $this->client->getResponse()->getContent());
    }

    public function testSuggestReturnsRelevantResult()
    {
        $this->cacheServiceMock->expects($this->once())
            ->method('zRange')
            ->with('dictionary_words', 0, -1, ['REV'])
            ->willReturn([
            'age', 'aged', 'agency', 'agenda', 'agent', 'aggravation', 'aggressive', 'ago',
            'agree', 'agreement', 'agriculture', 'ah', 'ahead', 'aid', 'aim', 'air',
            'airplane', 'airport', 'aisle', 'alacrity', 'alarm', 'alcohol', 'alert', 'alibi',
            'alimony', 'alive', 'all', 'allergy', 'allow', 'allowance', 'almost', 'alone'
        ]);
        static::getContainer()->set(CacheService::class, $this->cacheServiceMock);

        $this->client->request('GET', '/autocomplete?q=al');
        $this->assertSame(
            '["alacrity","alarm","alcohol","alert","alibi"]',
            $this->client->getResponse()->getContent()
        );
    }
}
