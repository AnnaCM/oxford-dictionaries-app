<?php

namespace App\EventSubscriber;

use App\Service\ExceptionHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private ExceptionHandler $exceptionHandler;

    public function __construct(ExceptionHandler $handler)
    {
        $this->exceptionHandler = $handler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = $this->exceptionHandler->handle($exception);

        if ($response) {
            $event->setResponse($response);
        }
    }
}
