<?php

namespace App\Tests\Controller;


use App\Service\ExceptionHandler;

class ErrorPageTest extends Base
{
    public function testAttemptingToAccessNonExistingEndpointRendersCustom404ErrorTemplate()
    {
        $mockHandler = $this->getMockBuilder(ExceptionHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        static::getContainer()->set(ExceptionHandler::class, $mockHandler);

        $this->client->request('GET', "/non-existent-endpoint");

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(404);

        $this->assertStringContainsString('TEST: CUSTOM TEMPLATE: error404.html.twig', $response->getContent());
    }
}
