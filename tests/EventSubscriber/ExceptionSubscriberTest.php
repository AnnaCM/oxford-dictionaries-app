<?php

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\ExceptionSubscriber;
use App\Service\ExceptionHandler;
use ColinODell\PsrTestLogger\TestLogger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

class ExceptionSubscriberTest extends WebTestCase
{
    public function testExceptionSubscriberHandles400AndLogsError()
    {
        $exceptionMessage = 'Invalid request';

        $mockTwig = $this->createMock(Environment::class);
        $mockTwig->expects($this->once())
            ->method('render')
            ->with('exceptions/error400.html.twig', ['exceptionMessage' => $exceptionMessage])
            ->willReturn('Rendered content');

        $mockLogger = new TestLogger();

        $subscriber = new ExceptionSubscriber(new ExceptionHandler($mockTwig, $mockLogger));

        $event = new ExceptionEvent(
            self::createKernel(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new BadRequestHttpException($exceptionMessage)
        );
        $exception = $event->getThrowable();

        $subscriber->onKernelException($event);

        $this->assertSame(400, $event->getResponse()->getStatusCode());
        $this->assertSame('Rendered content', $event->getResponse()->getContent());

        $this->assertTrue($mockLogger->hasErrorRecords());
        $this->assertTrue($mockLogger->hasErrorThatContains('Caught exception: '.$exceptionMessage));
        $this->assertTrue($mockLogger->hasErrorThatContains(basename($exception->getFile())));
        $this->assertTrue($mockLogger->hasErrorThatContains('Line: '.$exception->getLine()));
        $this->assertTrue($mockLogger->hasErrorThatContains('Exception trace:'));
        $this->assertTrue($mockLogger->hasErrorThatContains(self::class));
        $this->assertTrue($mockLogger->hasErrorThatContains(__FUNCTION__));
    }
}
