<?php

namespace App\Tests\Service;

use App\Entity\Translations as TranslationsEntity;
use App\Exception\NotFoundError;
use App\Exception\ValidationError;
use App\Service\CacheStore as CacheService;
use App\Service\Translations as TranslationsService;
use App\Tests\Service\Util\HttpMock;
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
            $this->mockHttpClient($this->endpoint, file_get_contents(__DIR__ . "/Fixtures/Translations/alert.json"), 200),
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

        $this->translationsService->getTranslations('fr', 'en', 'alert');
    }

    public function testGetTranslationsThrowsValidationErrorForInvalidTargetLang()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('Invalid code for target language: gu');
        $this->cacheServiceMock->expects($this->never())->method('get');
        $this->cacheServiceMock->expects($this->never())->method('set');

        $this->translationsService->getTranslations('en', 'gu', 'alert');
    }

    public function testGetTranslationsThrowsBadParameterException()
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('');
        $this->cacheServiceMock->expects($this->once())->method('get')->with('App\Service\Translations::getTranslations_ace_en_de')->willReturn(null);
        $this->cacheServiceMock->expects($this->never())->method('set');

        $definitionsService = new TranslationsService(
            $this->cacheServiceMock,
            $this->mockHttpClient($this->endpoint, '', 400),
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

        $definitionsService = new TranslationsService(
            $this->cacheServiceMock,
            $this->mockHttpClient($this->endpoint, '', 404),
            $this->endpoint,
            $this->appId,
            $this->appKey
        );

        $definitionsService->getTranslations('el', 'en', 'aaa');
    }

    public function testGetTranslationsReturnsValidResult()
    {
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->with('App\Service\Translations::getTranslations_alert_en_it')
            ->willReturn(null);
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('set')
            ->with(
                'App\Service\Translations::getTranslations_alert_en_it',
                json_decode(file_get_contents(__DIR__ . "/Fixtures/Translations/alert.json"))
            );

        $translations = $this->translationsService->getTranslations('en', 'it', 'alert');
        $this->assertResult($translations);
    }

    public function testGetTranslationsHitsCache()
    {
        $this->cacheServiceMock
            ->expects($this->once())
            ->method('get')
            ->with('App\Service\Translations::getTranslations_alert_en_it')
            ->willReturn(json_decode(file_get_contents(__DIR__ . "/Fixtures/Translations/alert.json")));
        $this->cacheServiceMock->expects($this->never())->method('set');

        $translations = $this->translationsService->getTranslations('en', 'it', 'alert');
        $this->assertResult($translations);
    }

    private function assertResult(object $translations)
    {
        $this->assertInstanceOf(TranslationsEntity::class, $translations);
        $this->assertSame('alert', $translations->text);
        $this->assertSame(
            [
                'UK' => [
                    'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/alert__gb_1_8.mp3',
                    'phoneticSpelling' => 'əˈləːt'
                ],
                'US' => [
                    'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/alert__us_1.mp3',
                    'phoneticSpelling' => 'əˈlərt'
                ]
            ],
            $translations->pronunciations
        );

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

        $this->assertEquals([
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
        ], $translations->senses);
    }
}
