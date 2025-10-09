<?php

namespace App\Tests\Controller;

use App\Entity\Definitions as DefinitionsEntity;
use App\Exception\NotFoundError;
use App\Service\Definitions as DefinitionsService;
use App\Service\ExceptionHandler;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DefinitionsTest extends Base
{
    private MockObject $definitionsServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->definitionsServiceMock = $this->getMockBuilder(DefinitionsService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider getPath
     */
    public function testIndex(string $path)
    {
        static::getContainer()->set(DefinitionsService::class, $this->definitionsServiceMock);

        $this->mockTwigTemplate(
            'definitions/index.html.twig',
            [
                'selectedSourceLang' => 'en-gb',
                'sourceLangs' => [
                    'en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish',
                    'fr' => 'French', 'gu' => 'Gujarati', 'hi' => 'Hindi', 'lv' => 'Latvian',
                    'ro' => 'Romanian', 'ta' => 'Tamil', 'zh' => 'Chinese'
                ]
            ]
        );

        $this->client->request('GET', $path);
    }

    public function getPath(): array
    {
        return [
            'homepage' => ['/'],
            'definitions' => ['/definitions'],
        ];
    }

    public function testDefinitions()
    {
        $sourceLang = 'en-gb';
        $word = 'ace';
        $definitionsEntity = new DefinitionsEntity();
        $definitionsEntity->text = $word;
        $definitionsEntity->pronunciations = [
            'UK' => [
                'phoneticSpelling' => 'eɪs',
                'audioFile' => 'https://audio.oxforddictionaries.com/en/mp3/ace__gb_3.mp3'
            ]
        ];
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
        $definitionsEntity->senses = [
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
        ];

        $this->definitionsServiceMock->expects($this->once())->method('getDefinitions')->with($sourceLang, $word)->willReturn($definitionsEntity);
        static::getContainer()->set(DefinitionsService::class, $this->definitionsServiceMock);

        $this->mockTwigTemplate(
            'definitions/content.html.twig',
            [
                'selectedSourceLang' => 'en-gb',
                'sourceLangs' => [
                    'en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish',
                    'fr' => 'French', 'gu' => 'Gujarati', 'hi' => 'Hindi', 'lv' => 'Latvian',
                    'ro' => 'Romanian', 'ta' => 'Tamil', 'zh' => 'Chinese'
                ],
                'text' => $word,
                'senses' => $definitionsEntity->senses,
                'sourceLangPhoneticSpelling' => $definitionsEntity->pronunciations['UK']['phoneticSpelling'],
                'sourceLangAudioFile' => $definitionsEntity->pronunciations['UK']['audioFile']
            ]
        );

        $this->client->request('GET', "/definitions/{$sourceLang}/{$word}");
    }

    public function testGetDefinitionsCatchesNotFoundExceptionAndRendersCustom404ErrorTemplate()
    {
        $sourceLang = 'es';
        $word = 'azuúcar';

        $this->definitionsServiceMock->expects($this->once())->method('getDefinitions')->with($sourceLang, $word)->willThrowException(new NotFoundError());
        static::getContainer()->set(DefinitionsService::class, $this->definitionsServiceMock);

        $this->mockTwigTemplate(
            'exceptions/error404.html.twig',
            [
                'selectedSourceLang' => $sourceLang,
                'sourceLangs' => [
                    'en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish',
                    'fr' => 'French', 'gu' => 'Gujarati', 'hi' => 'Hindi', 'lv' => 'Latvian',
                    'ro' => 'Romanian', 'ta' => 'Tamil', 'zh' => 'Chinese'
                ],
                'text' => $word
            ]
        );

        $this->client->request('GET', "/definitions/{$sourceLang}/{$word}");
    }

    public function testGetDefinitionsBypassesExceptionSubscriberAndRendersCustomErrorTemplate()
    {
        $sourceLang = 'fr';
        $word = 'agréable';

        $exception = new HttpException(500, 'Ops - Something went wrong!');

        $this->definitionsServiceMock->expects($this->once())->method('getDefinitions')->with($sourceLang, $word)->willThrowException($exception);
        static::getContainer()->set(DefinitionsService::class, $this->definitionsServiceMock);

        $mockHandler = $this->getMockBuilder(ExceptionHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockHandler->expects($this->once())
            ->method('handle')
            ->with($exception);
        static::getContainer()->set(ExceptionHandler::class, $mockHandler);

        $this->client->request('GET', "/definitions/{$sourceLang}/{$word}");

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(500);

        $this->assertStringContainsString('TEST: CUSTOM TEMPLATE: error.html.twig', $response->getContent());
    }
}
