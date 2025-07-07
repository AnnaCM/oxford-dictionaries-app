<?php

namespace App\Tests\Twig\Templates;

use App\Tests\Twig\Base;

class HomepageTest extends Base
{
    public function testIndexTemplate()
    {
        $haystack = $this->renderTemplate('homepage/index.html.twig');
        $this->assertStringContainsString('Definitions', $haystack);
        $this->assertStringContainsString('Translations', $haystack);
        $this->assertStringContainsString('<script src="/js/search_translations.js"></script>', $haystack);
        $this->assertStringContainsString('<script src="/js/search_definitions.js"></script>', $haystack);
        $this->assertExtendsBaseTemplate($haystack);
    }
}
