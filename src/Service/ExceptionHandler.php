<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Environment;

class ExceptionHandler
{
    public function __construct(
        private Environment $twig,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(\Throwable $exception): ?Response
    {
        $this->logger->error('Caught exception: '.$exception->getMessage());
        $this->logger->error('File: '.$exception->getFile());
        $this->logger->error('Line: '.$exception->getLine());
        $this->logger->error('Exception trace: '.$exception->getTraceAsString());

        if ($exception instanceof BadRequestHttpException) {
            return new Response(
                $this->twig->render('exceptions/error400.html.twig', [
                    'exceptionMessage' => $exception->getMessage(),
                ]),
                Response::HTTP_BAD_REQUEST
            );
        }

        return null;
    }
}
