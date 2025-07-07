<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ErrorPreviewController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->twig->addGlobal('layout_template', 'definitions/index.html.twig');
    }

    /**
     * @Route("/_error/{code}/{context}", name="error_preview", methods={"GET"})
     */
    public function show(int $code, string $context = null): Response
    {
        $template = sprintf('exceptions/error%d.html.twig', $code);
        if (!$this->twig->getLoader()->exists($template) || ($context == 'page')) {
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
