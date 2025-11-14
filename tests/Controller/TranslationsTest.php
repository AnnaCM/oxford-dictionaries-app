<?php

namespace App\Tests\Controller;

use App\Entity\Translations as TranslationsEntity;
use App\Exception\NotFoundError;
use App\Service\ExceptionHandler;
use App\Service\Translations as TranslationsService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TranslationsTest extends Base
{
    private MockObject $translationsServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translationsServiceMock = $this->getMockBuilder(TranslationsService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testIndex()
    {
        static::getContainer()->set(TranslationsService::class, $this->translationsServiceMock);

        $this->mockTwigTemplate(
            'translations/index.html.twig',
            [
                'selectedSourceLang' => 'en',
                'selectedTargetLang' => 'es',
                'sourceLangs' => [
                    'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                    'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'it' => 'Italian', 'mr' => 'Marathi',
                    'ms' => 'Malaysian', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'ru' => 'Russian', 'te' => 'Telugu',
                    'tt' => 'Tatar', 'zh' => 'Chinese',
                ],
                'targetLangs' => [
                    'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                    'fa' => 'Farsi', 'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'ig' => 'Igbo',
                    'it' => 'Italian', 'ka' => 'Georgian', 'mr' => 'Marathi', 'ms' => 'Malaysian', 'pt' => 'Portuguese',
                    'qu' => 'Quechua', 'ro' => 'Romanian', 'ru' => 'Russian', 'tg' => 'Tajik', 'tt' => 'Tatar',
                    'yo' => 'Yoruba', 'zh' => 'Chinese',
                ],
            ]
        );

        $this->client->request('GET', '/translations');
    }

    public function testTranslations()
    {
        $sourceLang = 'en';
        $targetLang = 'pt';
        $word = 'authentic';

        $collocations11 = new \stdClass();
        $collocations11->id = 'document';
        $collocations11->text = 'document';
        $collocations11->type = 'object';
        $collocations12 = new \stdClass();
        $collocations12->id = 'signature';
        $collocations12->text = 'signature';
        $collocations12->type = 'object';
        $collocations13 = new \stdClass();
        $collocations13->id = 'painting';
        $collocations13->text = 'painting';
        $collocations13->type = 'object';
        $translations11 = new \stdClass();
        $translations11->collocations = [$collocations11, $collocations12, $collocations13];
        $translations11->language = 'pt';
        $translations11->text = 'autêntico';
        $notes11 = new \stdClass();
        $notes11->text = 'genuine';
        $notes11->type = 'indicator';
        $collocations21 = new \stdClass();
        $collocations21->id = 'account';
        $collocations21->text = 'account';
        $collocations21->type = 'object';
        $collocations22 = new \stdClass();
        $collocations22->id = 'information';
        $collocations22->text = 'information';
        $collocations22->type = 'object';
        $translations21 = new \stdClass();
        $translations21->collocations = [$collocations21, $collocations22];
        $translations21->language = 'pt';
        $translations21->text = 'autêntico';
        $notes21 = new \stdClass();
        $notes21->text = 'reliable';
        $notes21->type = 'indicator';
        $translationsEntity = new TranslationsEntity();
        $translationsEntity->text = $word;
        $translationsEntity->pronunciations = [
            'UK' => [
                'phoneticSpelling' => 'ɔːˈθɛntɪk',
                'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/authentic_gb_1.mp3',
            ],
            'US' => [
                'phoneticSpelling' => 'ɔˈθɛn(t)ɪk',
                'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/authentic_us_1.mp3',
            ],
        ];
        $translationsEntity->senses = [
            'adjective' => [
                [
                    'translations' => [$translations11],
                    'notes' => [$notes11],
                ],
                [
                    'translations' => [$translations21],
                    'notes' => [$notes21],
                ],
            ],
        ];

        $this->translationsServiceMock->expects($this->once())->method('getTranslations')->with($sourceLang, $targetLang, $word)->willReturn($translationsEntity);
        static::getContainer()->set(TranslationsService::class, $this->translationsServiceMock);

        $this->mockTwigTemplate(
            'translations/content.html.twig',
            [
                'selectedSourceLang' => $sourceLang,
                'selectedTargetLang' => $targetLang,
                'sourceLangs' => [
                    'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                    'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'it' => 'Italian', 'mr' => 'Marathi',
                    'ms' => 'Malaysian', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'ru' => 'Russian', 'te' => 'Telugu',
                    'tt' => 'Tatar', 'zh' => 'Chinese',
                ],
                'targetLangs' => [
                    'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                    'fa' => 'Farsi', 'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'ig' => 'Igbo',
                    'it' => 'Italian', 'ka' => 'Georgian', 'mr' => 'Marathi', 'ms' => 'Malaysian', 'pt' => 'Portuguese',
                    'qu' => 'Quechua', 'ro' => 'Romanian', 'ru' => 'Russian', 'tg' => 'Tajik', 'tt' => 'Tatar',
                    'yo' => 'Yoruba', 'zh' => 'Chinese',
                ],
                'text' => $word,
                'senses' => $translationsEntity->senses,
                'pronunciations' => $translationsEntity->pronunciations,
            ]
        );

        $this->client->request('GET', "/translations/{$sourceLang}/{$targetLang}/{$word}");
    }

    public function testGetTranslationsCatchesNotFoundExceptionAndRendersCustomTemplate()
    {
        $sourceLang = 'es';
        $targetLang = 'ro';
        $word = 'añnaadir';
        $this->translationsServiceMock->expects($this->once())->method('getTranslations')->with($sourceLang, $targetLang, $word)->willThrowException(new NotFoundError());
        static::getContainer()->set(TranslationsService::class, $this->translationsServiceMock);

        $this->mockTwigTemplate(
            'exceptions/error404.html.twig',
            [
                'selectedSourceLang' => $sourceLang,
                'selectedTargetLang' => $targetLang,
                'sourceLangs' => [
                    'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                    'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'it' => 'Italian', 'mr' => 'Marathi',
                    'ms' => 'Malaysian', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'ru' => 'Russian', 'te' => 'Telugu',
                    'tt' => 'Tatar', 'zh' => 'Chinese',
                ],
                'targetLangs' => [
                    'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
                    'fa' => 'Farsi', 'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'ig' => 'Igbo',
                    'it' => 'Italian', 'ka' => 'Georgian', 'mr' => 'Marathi', 'ms' => 'Malaysian', 'pt' => 'Portuguese',
                    'qu' => 'Quechua', 'ro' => 'Romanian', 'ru' => 'Russian', 'tg' => 'Tajik', 'tt' => 'Tatar',
                    'yo' => 'Yoruba', 'zh' => 'Chinese',
                ],
                'text' => $word,
            ]
        );

        $this->client->request('GET', "/translations/{$sourceLang}/{$targetLang}/{$word}");
    }

    public function testGetTranslationsBypassesExceptionSubscriberAndRendersCustomErrorTemplate()
    {
        $sourceLang = 'en';
        $targetLang = 'fa';
        $word = 'a-argh';

        $exception = new HttpException(500, 'Ops - Something went wrong!');

        $this->translationsServiceMock->expects($this->once())->method('getTranslations')->with($sourceLang, $targetLang, $word)->willThrowException($exception);
        static::getContainer()->set(TranslationsService::class, $this->translationsServiceMock);

        $mockHandler = $this->getMockBuilder(ExceptionHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockHandler->expects($this->once())
            ->method('handle')
            ->with($exception);
        static::getContainer()->set(ExceptionHandler::class, $mockHandler);

        $this->client->request('GET', "/translations/{$sourceLang}/{$targetLang}/{$word}");

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(500);

        $this->assertStringContainsString('TEST: CUSTOM TEMPLATE: error.html.twig', $response->getContent());
    }
}
