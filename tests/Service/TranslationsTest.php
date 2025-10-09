<?php

namespace App\Tests\Service;

use App\Entity\Translations as TranslationsEntity;
use App\Exception\NotFoundError;
use App\Exception\ValidationError;
use App\Service\CacheStore as CacheService;
use App\Service\Translations as TranslationsService;
use App\Tests\Util\HttpMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslationsTest extends TestCase
{
    use HttpMock;

    private object $translationsService;
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

        $this->translationsService = new TranslationsService(
            $this->cacheServiceMock,
            $this->mockHttpClient(file_get_contents(__DIR__ . "/Fixtures/Translations/alert.json"), 200),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );
    }

    public function testGetTranslationsThrowsValidationErrorForInvalidSourceLang()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('Invalid code for source language: fr');
        $this->cacheServiceMock->expects($this->never())->method('get');
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $this->translationsService->getTranslations('fr', 'en', 'alert');
    }

    public function testGetTranslationsThrowsValidationErrorForInvalidTargetLang()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('Invalid code for target language: gu');
        $this->cacheServiceMock->expects($this->never())->method('get');
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $this->translationsService->getTranslations('en', 'gu', 'alert');
    }

    public function testGetTranslationsThrowsBadParameterException()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('');
        $this->cacheServiceMock->expects($this->once())->method('get')->with('App\Service\Translations::getTranslations_ace_en_de')->willReturn(null);
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $definitionsService = new TranslationsService(
            $this->cacheServiceMock,
            $this->mockHttpClient('', 400),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );

        $definitionsService->getTranslations('en', 'de', 'ace');
    }

    public function testGetTranslationsThrowsNotFoundException()
    {
        $this->expectException(NotFoundError::class);
        $this->expectExceptionMessage('');
        $this->cacheServiceMock->expects($this->once())->method('get')->with('App\Service\Translations::getTranslations_aaa_el_en')->willReturn(null);
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $definitionsService = new TranslationsService(
            $this->cacheServiceMock,
            $this->mockHttpClient('', 404),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );

        $definitionsService->getTranslations('el', 'en', 'aaa');
    }

    /**
     * @dataProvider getWordTranslations
     */
    public function testGetTranslationsReturnsValidResult(
        string $word,
        string $sourceLang,
        string $targetLang,
        array $result
    ) {
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->with("App\Service\Translations::getTranslations_{$word}_{$sourceLang}_{$targetLang}")
            ->willReturn(null);
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('set')
            ->with(
                "App\Service\Translations::getTranslations_{$word}_{$sourceLang}_{$targetLang}",
                json_decode(file_get_contents(__DIR__ . "/Fixtures/Translations/{$word}.json"))
            );
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('zIncrBy')
            ->with("{$sourceLang}_dictionary_words", 1, $word);

        if ($word != 'alert') {
            $translationsService = new TranslationsService(
                $this->cacheServiceMock,
                $this->mockHttpClient(file_get_contents(__DIR__ . "/Fixtures/Translations/{$word}.json"), 200),
                $this->endpoint,
                $this->appId,
                $this->appKey
            );
        } else {
            $translationsService = $this->translationsService;
        }

        $translations = $translationsService->getTranslations($sourceLang, $targetLang, $word);
        $this->assertInstanceOf(TranslationsEntity::class, $translations);
        $this->assertSame($result['id'] ?? $word, $translations->text);
        $this->assertSame($result['pronunciations'], $translations->pronunciations);
        $this->assertEquals($result['senses'], $translations->senses);
    }

    public function testGetTranslationsHitsCache()
    {
        [$word, $sourceLang, $targetLang, $result] = $this->getWordTranslations()['alert'];

        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->with("App\Service\Translations::getTranslations_{$word}_{$sourceLang}_{$targetLang}")
            ->willReturn(json_decode(file_get_contents(__DIR__ . "/Fixtures/Translations/{$word}.json")));
        $this->cacheServiceMock->expects($this->never())->method('set');
        $this->cacheServiceMock->expects($this->never())->method('zIncrBy');

        $translations = $this->translationsService->getTranslations($sourceLang, $targetLang, $word);
        $this->assertInstanceOf(TranslationsEntity::class, $translations);
        $this->assertSame($word, $translations->text);
        $this->assertSame($result['pronunciations'], $translations->pronunciations);
        $this->assertEquals($result['senses'], $translations->senses);
    }

    public function getWordTranslations(): array
    {
        $notes11 = new \stdClass();
        $notes11->text = 'lively';
        $notes11->type = 'indicator';
        $collocations11 = new \stdClass();
        $collocations11->id = 'child';
        $collocations11->text = 'child';
        $collocations11->type = 'object';
        $translations11 = new \stdClass();
        $translations11->collocations = [$collocations11];
        $translations11->language = 'it';
        $translations11->text = 'vivace, sveglio';
        $collocations12 = new \stdClass();
        $collocations12->id = 'old_person';
        $collocations12->text = 'old person';
        $collocations12->type = 'object';
        $translations12 = new \stdClass();
        $translations12->collocations = [$collocations12];
        $translations12->language = 'it';
        $translations12->text = 'arzillo';
        $translations21 = new \stdClass();
        $translations21->language = 'it';
        $translations21->text = 'vigile';
        $translations22 = new \stdClass();
        $translations22->language = 'it';
        $translations22->text = 'attento';
        $notes21 = new \stdClass();
        $notes21->text = 'attentive';
        $notes21->type = 'indicator';
        $examplesTranslationsCollocations21 = new \stdClass();
        $examplesTranslationsCollocations21->id = 'danger';
        $examplesTranslationsCollocations21->text = 'danger';
        $examplesTranslationsCollocations21->type = 'object';
        $examplesTranslationsCollocations22 = new \stdClass();
        $examplesTranslationsCollocations22->id = 'risk';
        $examplesTranslationsCollocations22->text = 'risk';
        $examplesTranslationsCollocations22->type = 'object';
        $examplesTranslationsCollocations23 = new \stdClass();
        $examplesTranslationsCollocations23->id = 'fact';
        $examplesTranslationsCollocations23->text = 'fact';
        $examplesTranslationsCollocations23->type = 'object';
        $examplesTranslationsCollocations24 = new \stdClass();
        $examplesTranslationsCollocations24->id = 'possibility';
        $examplesTranslationsCollocations24->text = 'possibility';
        $examplesTranslationsCollocations24->type = 'object';
        $examplesTranslations21 = new \stdClass();
        $examplesTranslations21->collocations = [
            $examplesTranslationsCollocations21,
            $examplesTranslationsCollocations22,
            $examplesTranslationsCollocations23,
            $examplesTranslationsCollocations24
        ];
        $examplesTranslations21->language = 'it';
        $examplesTranslations21->text = 'essere consapevole di';
        $examples21 = new \stdClass();
        $examples21->text = 'to be alert to';
        $examples21->translations = [$examplesTranslations21];
        $translations31 = new \stdClass();
        $translationsGrammaticalFeatures31 = new \stdClass();
        $translationsGrammaticalFeatures31->id = 'masculine';
        $translationsGrammaticalFeatures31->text = 'Masculine';
        $translationsGrammaticalFeatures31->type = 'Gender';
        $translations31->grammaticalFeatures = [$translationsGrammaticalFeatures31];
        $translations31->language = 'it';
        $translations31->text = 'allarme';
        $examplesTranslationsCollocations31 = new \stdClass();
        $examplesTranslationsCollocations31->id = 'danger';
        $examplesTranslationsCollocations31->text = 'danger';
        $examplesTranslationsCollocations31->type = 'object';
        $examplesTranslations31 = new \stdClass();
        $examplesTranslations31->collocations = [$examplesTranslationsCollocations31];
        $examplesTranslations31->language = 'it';
        $examplesTranslations31->text = 'stare in guardia contro';
        $examples31 = new \stdClass();
        $examples31->text = 'to be on the alert for';
        $examples31->translations = [$examplesTranslations31];
        $examplesTranslations32 = new \stdClass();
        $examplesTranslations32->language = 'it';
        $examplesTranslations32->text = 'allarme antincendio, allarme bomba';
        $examples32 = new \stdClass();
        $examples32->text = 'fire, bomb alert';
        $examples32->translations = [$examplesTranslations32];
        $examplesTranslations33 = new \stdClass();
        $examplesTranslations33->language = 'it';
        $examplesTranslations33->text = 'allarme di sicurezza';
        $examples33 = new \stdClass();
        $examples33->text = 'security alert';
        $examples33->translations = [$examplesTranslations33];
        $examplesTranslations34 = new \stdClass();
        $examplesTranslations34->language = 'it';
        $examplesTranslations34->text = 'essere in stato di massima allerta';
        $examples34 = new \stdClass();
        $examples34->text = 'to be on (red) alert (Military) or on full alert';
        $examples34->translations = [$examplesTranslations34];
        $translations41 = new \stdClass();
        $translations41->language = 'it';
        $translations41->text = 'allertare, mettere in stato d\'allerta';
        $examplesTranslationsCollocations51 = new \stdClass();
        $examplesTranslationsCollocations51->id = 'danger';
        $examplesTranslationsCollocations51->text = 'danger';
        $examplesTranslationsCollocations51->type = 'object';
        $examplesTranslations51 = new \stdClass();
        $examplesTranslations51->collocations = [$examplesTranslationsCollocations51];
        $examplesTranslations51->language = 'it';
        $examplesTranslations51->text = 'mettere qcn in guardia contro';
        $examplesTranslationsCollocations52 = new \stdClass();
        $examplesTranslationsCollocations52->id = 'fact';
        $examplesTranslationsCollocations52->text = 'fact';
        $examplesTranslationsCollocations52->type = 'object';
        $examplesTranslationsCollocations53 = new \stdClass();
        $examplesTranslationsCollocations53->id = 'situation';
        $examplesTranslationsCollocations53->text = 'situation';
        $examplesTranslationsCollocations53->type = 'object';
        $examplesTranslations52 = new \stdClass();
        $examplesTranslations52->collocations = [$examplesTranslationsCollocations52, $examplesTranslationsCollocations53];
        $examplesTranslations52->language = 'it';
        $examplesTranslations52->text = 'richiamare l\'attenzione di qcn su';
        $examples51 = new \stdClass();
        $examples51->text = 'to alert sb to';
        $examples51->translations = [$examplesTranslations51, $examplesTranslations52];

        $alertResult = [
            'pronunciations' => [
                'UK' => [
                    'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/alert__gb_1_8.mp3',
                    'phoneticSpelling' => 'əˈləːt'
                ],
                'US' => [
                    'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/alert__us_1.mp3',
                    'phoneticSpelling' => 'əˈlərt'
                ]
            ],
            'senses' => [
                'adjective' => [
                    [
                        'translations' => [$translations11, $translations12],
                        'notes' => [$notes11]
                    ],
                    [
                        'translations' => [$translations21, $translations22],
                        'notes' => [$notes21],
                        'examples' => [$examples21]
                    ]
                ],
                'noun' => [
                    [
                        'translations' => [$translations31],
                        'examples' => [
                            $examples31,
                            $examples32,
                            $examples33,
                            $examples34
                        ]
                    ]
                ],
                'verb' => [
                    [
                        'translations' => [$translations41],
                    ],
                    [
                        'examples' => [$examples51]
                    ]
                ]
            ],
        ];

        $crossReferences11 = new \stdClass();
        $crossReferences11->id = 'advertisement';
        $crossReferences11->text = 'advertisement';
        $crossReferences11->type = 'see also';

        $notes11 = new \stdClass();
        $notes11->text = 'written form';
        $notes11->type = 'indicator';

        $translations11 = new \stdClass();
        $translations11->type = 'direct';
        $translations11->language = 'es';
        $translations11->text = 'despuu00e9s de Cristo';
        $translations12 = new \stdClass();
        $translations12->type = 'direct';
        $translations12->language = 'es';
        $translations12->text = 'dC';
        $translations12->notes = [$notes11];
        $translations13 = new \stdClass();
        $translations13->type = 'direct';
        $translations13->language = 'es';
        $translations13->text = 'd. de C.';
        $translations13->notes = [$notes11];
        $translations14 = new \stdClass();
        $translations14->type = 'direct';
        $translations14->language = 'es';
        $translations14->text = 'd. de J. C.';
        $translations14->notes = [$notes11];

        $adResult = [
            'pronunciations' => [
                'US' => [
                    'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/ad__1_us_1.mp3',
                    'phoneticSpelling' => 'u00e6d'
                ],
                'UK' => [
                    'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/add__gb_2.mp3',
                    'phoneticSpelling' => 'ad'
                ],
            ],
            'senses' => [
                'noun' => [
                    [
                        'notes' => [$crossReferences11],
                    ]
                ],
                'adverb' => [
                    [
                        'translations' => [$translations11, $translations12, $translations13, $translations14],
                        'definitions' => ['Anno Domini'],
                    ]
                ],
            ],
        ];

        $translations11 = new \stdClass();
        $translations11->language = 'el';
        $translations11->text = 'u03bau03b1u03c4u03acu03bbu03bbu03b7u03bbu03bfu03c2';
        $translations21 = new \stdClass();
        $translations21->language = 'el';
        $translations21->text = 'u03bfu03b9u03bau03b5u03b9u03bfu03c0u03bfu03b9u03bfu03cdu03bcu03b1u03b9';

        $appropriateResult = [
            'pronunciations' => [
                ['phoneticSpelling' => 'u0259\'pru0259u028apru026au0259t'],
            ],
            'senses' => [
                'adjective' => [
                    [
                        'translations' => [$translations11],
                    ]
                ],
                'verb' => [
                    [
                        'translations' => [$translations21],
                    ]
                ],
            ],
        ];

        $translations11 = new \stdClass();
        $translations11->language = 'en';
        $translations11->text = 'so that …';
        $translations12 = new \stdClass();
        $translations12->language = 'en';
        $translations12->text = 'in order that …';
        $examplesTranslations11 = new \stdClass();
        $examplesTranslations11->language = 'en';
        $examplesTranslations11->text = 'to take measures so that young people might find work';
        $examples11 = new \stdClass();
        $examples11->text = 'prendere delle misure affinché i giovani trovino lavoro';
        $examples11->translations = [$examplesTranslations11];
        $examplesTranslations12 = new \stdClass();
        $examplesTranslations12->language = 'en';
        $examplesTranslations12->text = 'she fixed the party for 3 so that Francesco could come';
        $examples12 = new \stdClass();
        $examples12->text = 'fissò la festa per le 3 affinché Francesco potesse venire';
        $examples12->translations = [$examplesTranslations12];
        $examplesTranslations13 = new \stdClass();
        $examplesTranslations13->language = 'en';
        $examplesTranslations13->text = 'to pay sb to do sth';
        $examples13 = new \stdClass();
        $examples13->text = 'pagare qcn affinché faccia qcs';
        $examples13->translations = [$examplesTranslations13];
        $examplesTranslations14 = new \stdClass();
        $examplesTranslations14->language = 'en';
        $examplesTranslations14->text = 'to be on one\'s guard against sth happening, to guard against sth happening';
        $examples14 = new \stdClass();
        $examples14->text = 'stare in guardia affinché qcs non accada';
        $examples14->translations = [$examplesTranslations14];

        $affinchéResult = [
            'id' => 'affinche',
            'pronunciations' => [
                ['phoneticSpelling' => 'affinˈke'],
            ],
            'senses' => [
                'conjunction' => [
                    [
                        'translations' => [
                            $translations11,
                            $translations12,
                        ],
                        'examples' => [
                            $examples11,
                            $examples12,
                            $examples13,
                            $examples14,
                        ]
                    ]
                ]
            ],
        ];

        return [
            'alert' => ['alert', 'en', 'it', $alertResult],
            'ad' => ['ad', 'en', 'es', $adResult],
            'appropriate' => ['appropriate', 'en', 'el', $appropriateResult],
            'affinché' => ['affinché', 'it', 'en', $affinchéResult],
        ];
    }
}
