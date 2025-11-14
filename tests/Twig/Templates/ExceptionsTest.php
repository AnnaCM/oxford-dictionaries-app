<?php

namespace App\Tests\Twig\Templates;

use App\Tests\Twig\Base;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class ExceptionsTest extends Base
{
    public function testCustom404PageIsRendered()
    {
        $options = ['text' => 'aaa'];
        $haystack = $this->renderTemplate('exceptions/error404.html.twig', $options);
        $this->assertStringContainsString('<h2 class="content-heading">aaa</h2>', $haystack);
        $this->assertStringContainsString('<p class="content-heading">No dictionary entry found for \'aaa\'</p>', $haystack);
    }

    public function testCustom400PageIsRendered()
    {
        $exception = FlattenException::create(new \Exception("Missing 'sourceLang' parameter"), 400);

        $haystack = $this->renderTemplate('exceptions/error400.html.twig', [
            'exceptionMessage' => $exception->getMessage(),
        ]);
        $this->assertStringContainsString('<h2 class="error-heading">4😵0 - Invalid request</h2>', $haystack);
        $this->assertStringContainsString('<p class="error-message">Missing &#039;sourceLang&#039; parameter</p>', $haystack);
    }

    public function testGeneralCustom404ErrorIsRendered()
    {
        $haystack = $this->renderTemplate('bundles/TwigBundle/Exception/error404.html.twig');
        $this->assertStringContainsString('<h2 class="error-heading">4😮4</h2>', $haystack);
        $this->assertStringContainsString('<p class="error-message">The page you requested could not be found.</p>', $haystack);
    }

    public function testGeneralCustomErrorIsRendered()
    {
        $haystack = $this->renderTemplate('bundles/TwigBundle/Exception/error.html.twig');
        $this->assertStringContainsString('<h2 class="error-heading">Something went wrong</h2>', $haystack);
        $this->assertStringContainsString('<p class="error-message">We\'re aware of the issue and working on a fix.</p>', $haystack);
        $this->assertStringContainsString('<p class="error-message">We\'re sorry for any inconvenience this is causing you.</p>', $haystack);
    }
}
