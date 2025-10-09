<?php

namespace App\Tests\Service;

use App\Entity\Definitions as DefinitionsEntity;
use App\Exception\NotFoundError;
use App\Exception\ValidationError;
use App\Service\CacheStore as CacheService;
use App\Service\Definitions as DefinitionsService;
use App\Tests\Util\HttpMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefinitionsTest extends TestCase
{
    use HttpMock;

    private DefinitionsService $definitionsService;
    private string $endpoint;
    private string $appId;
    private string $appKey;
    private MockObject $cacheServiceMock;

    protected function setUp(): void
    {
        $this->endpoint = 'https://api.exampledictionaries.com/api';
        $this->appId = 'APP_ID';
        $this->appKey = 'APP_KEY';

        $this->cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->definitionsService = new DefinitionsService(
            $this->cacheServiceMock,
            $this->mockHttpClient(file_get_contents(__DIR__ . "/Fixtures/Definitions/ace.json"), 200),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );
    }

    public function testGetDefinitionsThrowsValidationErrorForInvalidSourceLang()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('Invalid code for source language: it');
        $this->cacheServiceMock->expects($this->never())->method('get');
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $this->definitionsService->getDefinitions('it', 'ace');
    }

    public function testGetDefinitionsThrowsBadParameterException()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('');
        $this->cacheServiceMock->expects($this->once())->method('get')->with('App\Service\Definitions::getDefinitions_ace_es')->willReturn(null);
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $definitionsService = new DefinitionsService(
            $this->cacheServiceMock,
            $this->mockHttpClient('', 400),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );

        $definitionsService->getDefinitions('es', 'ace');
    }

    public function testGetDefinitionsThrowsNotFoundException()
    {
        $this->expectException(NotFoundError::class);
        $this->expectExceptionMessage('');
        $this->cacheServiceMock->expects($this->once())->method('get')->with('App\Service\Definitions::getDefinitions_aaa_es')->willReturn(null);
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $definitionsService = new DefinitionsService(
            $this->cacheServiceMock,
            $this->mockHttpClient('', 404),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );

        $definitionsService->getDefinitions('es', 'aaa');
    }

    /**
     * @dataProvider getWordDefinitions
     */
    public function testGetDefinitionsReturnsValidResult(
        string $word,
        string $sourceLang,
        array $result
    ) {
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->with('App\Service\Definitions::getDefinitions_' . $word . '_' . $sourceLang)
            ->willReturn(null);
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('set')
            ->with(
                'App\Service\Definitions::getDefinitions_' . $word . '_' . $sourceLang,
                json_decode(file_get_contents(__DIR__ . "/Fixtures/Definitions/{$word}.json"))
            );
        $keyPrefix = explode('-', $sourceLang)[0];
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('zIncrBy')
            ->with("{$keyPrefix}_dictionary_words", 1, $word);

        if ($word != 'ace') {
            $definitionsService = new DefinitionsService(
                $this->cacheServiceMock,
                $this->mockHttpClient(file_get_contents(__DIR__ . "/Fixtures/Definitions/{$word}.json"), 200),
                $this->endpoint,
                $this->appId,
                $this->appKey
            );
        } else {
            $definitionsService = $this->definitionsService;
        }

        $definitions = $definitionsService->getDefinitions($sourceLang, $word);
        $this->assertInstanceOf(DefinitionsEntity::class, $definitions);
        $this->assertSame($word, $definitions->text);
        if ($definitions->pronunciations) {
            $this->assertSame($result['pronunciations'], $definitions->pronunciations);
        }
        $this->assertEquals($result['senses'], $definitions->senses);
    }

    public function testGetDefinitionsHitsCache()
    {
        [$word, $sourceLang, $result] = $this->getWordDefinitions()['ace'];

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->with('App\Service\Definitions::getDefinitions_' . $word . '_' . $sourceLang)
            ->willReturn(json_decode(file_get_contents(__DIR__ . "/Fixtures/Definitions/{$word}.json")));
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $definitions = $this->definitionsService->getDefinitions($sourceLang, $word);
        $this->assertInstanceOf(DefinitionsEntity::class, $definitions);
        $this->assertSame($word, $definitions->text);
        $this->assertSame($result['pronunciations'], $definitions->pronunciations);
        $this->assertEquals($result['senses'], $definitions->senses);
    }

    public function getWordDefinitions(): array
    {
        $example11 = new \stdClass();
        $example11->text = "the ace of diamonds";
        $example12 = new \stdClass();
        $register11 = new \stdClass();
        $register11->id = "figurative";
        $register11->text = "Figurative";
        $example12->registers = [$register11];
        $example12->text = "life had started dealing him aces again";
        $example21 = new \stdClass();
        $example21->text = "a motorcycle ace";
        $example31 = new \stdClass();
        $example31->text = "Nadal banged down eight aces in the set";
        $example32 = new \stdClass();
        $example32->text = "both asexual, they have managed to connect with other aces offline";
        $example41 = new \stdClass();
        $example41->text = "an ace swimmer";
        $example42 = new \stdClass();
        $notes11 = new \stdClass();
        $notes11->text = "as exclamation";
        $notes11->type = "grammaticalNote";
        $example42->notes = [$notes11];
        $example42->text = "Ace! You've done it!";
        $example43 = new \stdClass();
        $example43->text = "I didn't realize that I was ace for a long time";
        $example51 = new \stdClass();
        $example51->text = "he can ace opponents with serves of no more than 62 mph";
        $example61 = new \stdClass();
        $example61->text = "I aced my grammar test";

        $aceResult = [
            'pronunciations' => [
                'UK' => [
                    'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/ace__gb_3.mp3',
                    'phoneticSpelling' => 'eɪs'
                ]
            ],
            'senses' => [
                'noun' => [
                    [
                        'definitions' => [
                            "a playing card with a single spot on it, ranked as the highest card in its suit in most card games"
                        ],
                        'examples' => [$example11, $example12]
                    ],
                    [
                        'definitions' => ["a person who excels at a particular sport or other activity"],
                        'examples' => [$example21]
                    ],
                    [
                        'definitions' => ["(in tennis and similar games) a service that an opponent is unable to return and thus wins a point"],
                        'examples' => [$example31]
                    ],
                    [
                        'definitions' => ["an asexual person"],
                        'examples' => [$example32]
                    ]
                ],
                'adjective' => [
                    [
                        'definitions' => ["very good"],
                        'examples' => [$example41, $example42]
                    ],
                    [
                        'definitions' => ["(of a person) asexual"],
                        'examples' => [$example43]
                    ]
                ],
                'verb' => [
                    [
                        'definitions' => ["(in tennis and similar games) serve an ace against (an opponent)"],
                        'examples' => [$example51]
                    ],
                    [
                        'definitions' => ["achieve high marks in (a test or exam)"],
                        'examples' => [$example61]
                    ]
                ]
            ],
        ];

        $example11 = new \stdClass();
        $example11->text = "my lack of artistic ability";

        $artisticResult = [
            'pronunciations' => [
                'UK' => [
                    'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/artistic__gb_1.mp3',
                    'phoneticSpelling' => 'u0251u02d0u02c8tu026astu026ak'
                ]],
            'senses' => [
                'adjective' => [
                    [
                        'definitions' => ["having or revealing natural creative skill"],
                        'examples' => [$example11],
                    ]
                ]
            ],
        ];

        $example11 = new \stdClass();
        $example11->text = "Il a amélioré ce moteur.";
        $example21 = new \stdClass();
        $example21->text = "Ses notes se sont améliorées.";

        $améliorerResult = [
            'senses' => [
                'verb' => [
                    [
                        'definitions' => ["Rendre meilleur."],
                        'examples' => [$example11],
                    ],
                    [
                        'definitions' => ["Devenir meilleur."],
                        'examples' => [$example21],
                    ]
                ]
            ],
        ];

        return [
            'ace' => ['ace', 'en-gb', $aceResult],
            'artistic' => ['artistic', 'en-gb', $artisticResult],
            'améliorer' => ['améliorer', 'fr', $améliorerResult],
        ];
    }
}
