<?php

namespace App\Tests\Service;

use App\Entity\Definitions as DefinitionsEntity;
use App\Exception\NotFoundError;
use App\Exception\ValidationError;
use App\Service\Definitions as DefinitionsService;
use App\Tests\Service\Util\HttpMock;
use PHPUnit\Framework\TestCase;

class DefinitionsTest extends TestCase
{
    use HttpMock;

    private DefinitionsService $definitionsService;
    private string $endpoint;
    private string $appId;
    private string $appKey;

    protected function setUp(): void
    {
        $this->endpoint = 'https://api.exampledictionaries.com/api';
        $this->appId = 'APP_ID';
        $this->appKey = 'APP_KEY';

        $this->definitionsService = new DefinitionsService(
            $this->mockHttpClient($this->endpoint, file_get_contents(__DIR__ . "/Fixtures/Definitions/ace.json"), 200),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );
    }

    public function testGetDefinitionsThrowsValidationErrorForInvalidSourceLang()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('Invalid code for source language: it');

        $this->definitionsService->getDefinitions('it', 'ace');
    }

    public function testGetDefinitionsThrowsBadParameterException()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('');

        $definitionsService = new DefinitionsService(
            $this->mockHttpClient($this->endpoint, '', 400),
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

        $definitionsService = new DefinitionsService(
            $this->mockHttpClient($this->endpoint, '', 404),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );

        $definitionsService->getDefinitions('es', 'aaa');
    }

    public function testGetDefinitionsReturnsValidResult()
    {
        $definitions = $this->definitionsService->getDefinitions('en-gb', 'ace');
        $this->assertInstanceOf(DefinitionsEntity::class, $definitions);
        $this->assertSame('ace', $definitions->text);
        $this->assertSame(['UK' => ['audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/ace__gb_3.mp3', 'phoneticSpelling' => 'eɪs']], $definitions->pronunciations);

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

        $this->assertEquals([
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
        ], $definitions->senses);
    }
}
