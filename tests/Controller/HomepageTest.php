<?php

namespace App\Tests\Controller;

class HomepageTest extends Base
{
    public function testIndex()
    {
        $this->mockTwigTemplate('homepage/index.html.twig', []);

        $this->client->request('GET', '/');
    }
}
