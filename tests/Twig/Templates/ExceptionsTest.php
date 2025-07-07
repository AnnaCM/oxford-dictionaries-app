<?php

namespace App\Tests\Twig\Templates;

use App\Tests\Twig\Base;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class ExceptionsTest extends Base
{
    /**
     * @dataProvider getGlobal
    */
    public function testCustom404PageIsRendered(string $layoutTemplateValue, array $options, string $assertMethod)
    {
        $this->twig->addGlobal('layout_template', $layoutTemplateValue);

        $haystack = $this->renderTemplate('exceptions/error404.html.twig', $options);
        $this->assertStringContainsString($options['text'], $haystack);
        $this->assertStringContainsString("No dictionary entry found for '{$options['text']}'", $haystack);
        $this->$assertMethod($haystack);
    }

    public function getGlobal(): array
    {
        $word = 'aaa';
        $definitionsOptions = [
            'text' => $word,
            'selectedSourceLang' => 'en-gb',
            'sourceLangs' => [
                'en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish',
                'fr' => 'French', 'gu' => 'Gujarati', 'hi' => 'Hindi', 'lv' => 'Latvian',
                'ro' => 'Romanian', 'ta' => 'Tamil', 'zh' => 'Chinese'
            ]
        ];

        $translationsOptions = $definitionsOptions;
        $translationsOptions['selectedSourceLang'] = 'en';
        $translationsOptions['selectedTargetLang'] = 'it';
        $translationsOptions['sourceLangs'] = [
            'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
            'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'it' => 'Italian', 'mr' => 'Marathi',
            'ms' => 'Malaysian', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'ru' => 'Russian', 'te' => 'Telugu',
            'tt' => 'Tatar', 'zh' => 'Chinese'
        ];
        $translationsOptions['targetLangs'] = [
            'en' => 'English', 'ar' => 'Arabic', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish',
            'fa' => 'Farsi', 'ha' => 'Hausa', 'hi' => 'Hindi', 'id' => 'Indonesian', 'ig' => 'Igbo',
            'it' => 'Italian', 'ka' => 'Georgian', 'mr' => 'Marathi', 'ms' => 'Malaysian', 'pt' => 'Portuguese',
            'qu' => 'Quechua', 'ro' => 'Romanian', 'ru' => 'Russian', 'tg' => 'Tajik', 'tt' => 'Tatar',
            'yo' => 'Yoruba', 'zh' => 'Chinese'
        ];

        return [
            ['definitions/index.html.twig', $definitionsOptions, 'assertContentDefinitionsIndexTemplateIsRendered'],
            ['translations/index.html.twig', $translationsOptions, 'assertContentTranslationsIndexTemplateIsRendered']
        ];
    }

    public function testCustom400PageIsRendered()
    {
        $exception = FlattenException::create(new \Exception("Missing 'sourceLang' parameter"), 400);

        $haystack = $this->renderTemplate('exceptions/error400.html.twig', [
            'exceptionMessage' => $exception->getMessage()
        ]);
        $this->assertStringContainsString('4😵0 - Invalid request', $haystack);
        $this->assertStringContainsString('Missing &#039;sourceLang&#039; parameter', $haystack);
        $this->assertExtendsBaseTemplate($haystack);
    }

    public function testGeneralCustom404ErrorIsRendered()
    {
        $haystack = $this->renderTemplate('bundles/TwigBundle/Exception/error404.html.twig');
        $this->assertStringContainsString('4😮4', $haystack);
        $this->assertStringContainsString('The page you requested could not be found.', $haystack);
        $this->assertExtendsBaseTemplate($haystack);
    }

    public function testGeneralCustomErrorIsRendered()
    {
        $haystack = $this->renderTemplate('bundles/TwigBundle/Exception/error.html.twig');
        $this->assertStringContainsString('Something went wrong', $haystack);
        $this->assertStringContainsString('We\'re aware of the issue and working on a fix.', $haystack);
        $this->assertStringContainsString('We\'re sorry for any inconvenience this is causing you.', $haystack);
        $this->assertExtendsBaseTemplate($haystack);
    }
}
