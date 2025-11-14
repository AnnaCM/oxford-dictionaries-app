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

        $this->client->request('GET', '/autocomplete?q=a&l=en');
        $this->assertSame('[]', $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getQueryParams
     */
    public function testSuggestReturnsRelevantResult(
        string $query,
        string $sourceLang,
        array $results,
        string $expectedJsonResult,
    ) {
        $this->cacheServiceMock->expects($this->once())
            ->method('zRange')
            ->with("{$sourceLang}_dictionary_words", 0, -1, ['REV'])
            ->willReturn($results);
        static::getContainer()->set(CacheService::class, $this->cacheServiceMock);

        $this->client->request('GET', "/autocomplete?q={$query}&l={$sourceLang}");
        $this->assertSame($expectedJsonResult, $this->client->getResponse()->getContent());
    }

    public static function getQueryParams(): array
    {
        $enResults = [
            'age', 'aged', 'agency', 'agenda', 'agent', 'aggravation', 'aggressive', 'ago',
            'agree', 'agreement', 'agriculture', 'ah', 'ahead', 'aid', 'aim', 'air',
            'airplane', 'airport', 'aisle', 'alacrity', 'alarm', 'alcohol', 'alert', 'alibi',
            'alimony', 'alive', 'all', 'allergy', 'allow', 'allowance', 'almost', 'alone',
        ];
        $expectedEnJsonResult = '["alacrity","alarm","alcohol","alert","alibi"]';

        $frResults = [
            'aîné', 'améliorer', 'aimer', 'aéroport', 'automne', 'architecture', 'apéro',
            'anecdoté', 'ananas', 'amour', 'amie', 'ami', 'alphabet', 'allée', 'ajouter',
            'aimable', 'aiguille', 'agréable', 'affamé', 'adorable', 'addition', 'actrice',
            'acrobatie', 'accordéon', 'absolument', 'abricot', 'abeille',
        ];
        $expectedFrJsonResult = '["am\u00e9liorer","amour","amie","ami"]';

        return [
            'en source language' => ['al', 'en', $enResults, $expectedEnJsonResult],
            'fr source language with accented characters' => ['am', 'fr', $frResults, $expectedFrJsonResult],
        ];
    }
}
