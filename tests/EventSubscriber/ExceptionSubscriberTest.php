<?php

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\ExceptionSubscriber;
use App\Service\ExceptionHandler;
use ColinODell\PsrTestLogger\TestLogger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
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

        $subscriber->onKernelException($event);

        $this->assertSame(400, $event->getResponse()->getStatusCode());
        $this->assertSame('Rendered content', $event->getResponse()->getContent());

        $this->assertTrue($mockLogger->hasErrorRecords());
        $this->assertTrue($mockLogger->hasErrorThatContains('Caught exception: Invalid request'));
        $this->assertTrue($mockLogger->hasErrorThatContains('File: /Volumes/Projects/oxford-dictionaries-app/tests/EventSubscriber/ExceptionSubscriberTest.php'));
        $this->assertTrue($mockLogger->hasErrorThatContains('Line: 35'));
        $this->assertTrue($mockLogger->hasErrorThatContains('Exception trace: #0 /Volumes/Projects/oxford-dictionaries-app/vendor/phpunit/phpunit/src/Framework/TestCase.php(1548): App\Tests\EventSubscriber\ExceptionSubscriberTest->testExceptionSubscriberHandles400AndLogsError()'));
    }
}
