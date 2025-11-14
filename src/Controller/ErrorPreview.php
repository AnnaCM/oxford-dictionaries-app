<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class ErrorPreview
{
    public function __construct(private Environment $twig)
    {
    }

    #[Route('/_error/{code}/{context}', name: 'error_preview', methods: ['GET'])]
    public function show(int $code, ?string $context = null): Response
    {
        $template = sprintf('exceptions/error%d.html.twig', $code);
        if (!$this->twig->getLoader()->exists($template) || ('page' == $context)) {
            $template = sprintf('bundles/TwigBundle/Exception/error%d.html.twig', $code);
        }

        if (!$this->twig->getLoader()->exists($template)) {
            $template = 'bundles/TwigBundle/Exception/error.html.twig';
        }

        return new Response(
            $this->twig->render($template, [
                // exceptions/error400.html.twig
                'exceptionMessage' => 'Invalid code for source language: fr',
                // exceptions/error404.html.twig
                'text' => 'aaa',
                'selectedSourceLang' => 'en_gb',
                'sourceLangs' => ['en-gb' => 'English', 'en-us' => 'American', 'es' => 'Spanish'],
            ]),
            $code
        );
    }
}
