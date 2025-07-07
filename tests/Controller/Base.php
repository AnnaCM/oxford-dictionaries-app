<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Twig\Environment;

class Base extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient(['debug' => false]);
        $this->client->catchExceptions(true);
    }

    protected function mockTwigTemplate(string $template, array $context): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with($this->equalTo($template), $this->equalTo($context));

        static::getContainer()->set(Environment::class, $twig);
    }
}
