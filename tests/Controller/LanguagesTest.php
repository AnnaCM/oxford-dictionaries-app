<?php

namespace App\Tests\Controller;

use App\Service\Definitions as DefinitionsService;
use App\Service\Translations as TranslationsService;
use PHPUnit\Framework\MockObject\MockObject;

class LanguagesTest extends Base
{
    private MockObject $definitionsServiceMock;
    private MockObject $translationsServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $definitionsServiceMock = $this->getMockBuilder(DefinitionsService::class)
            ->disableOriginalConstructor()
            ->getMock();
        static::getContainer()->set(DefinitionsService::class, $definitionsServiceMock);

        $translationsServiceMock = $this->getMockBuilder(TranslationsService::class)
            ->disableOriginalConstructor()
            ->getMock();
        static::getContainer()->set(TranslationsService::class, $translationsServiceMock);
    }

    public function testGetDefinitionsLanguages()
    {
        $this->client->request('GET', '/get-languages/definitions');
        $this->assertSame(
            '{"selectedSourceLang":"en-gb","sourceLangs":{"en-gb":"English","en-us":"American","es":"Spanish","fr":"French","gu":"Gujarati","hi":"Hindi","lv":"Latvian","ro":"Romanian","ta":"Tamil","zh":"Chinese"}}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testGetTranslationsLanguages()
    {
        $this->client->request('GET', '/get-languages/translations');
        $this->assertSame(
            '{"selectedSourceLang":"en","selectedTargetLang":"es","sourceLangs":{"en":"English","ar":"Arabic","de":"German","el":"Greek","es":"Spanish","ha":"Hausa","hi":"Hindi","id":"Indonesian","it":"Italian","mr":"Marathi","ms":"Malaysian","pt":"Portuguese","qu":"Quechua","ru":"Russian","te":"Telugu","tt":"Tatar","zh":"Chinese"},"targetLangs":{"en":"English","ar":"Arabic","de":"German","el":"Greek","es":"Spanish","fa":"Farsi","ha":"Hausa","hi":"Hindi","id":"Indonesian","ig":"Igbo","it":"Italian","ka":"Georgian","mr":"Marathi","ms":"Malaysian","pt":"Portuguese","qu":"Quechua","ro":"Romanian","ru":"Russian","tg":"Tajik","tt":"Tatar","yo":"Yoruba","zh":"Chinese"}}',
            $this->client->getResponse()->getContent()
        );
    }
}
