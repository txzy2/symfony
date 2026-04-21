<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestControllerTest extends WebTestCase
{
    public function testSuccessResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/test', ['name' => 'Anton']);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Hello Anton', $data['message']);
    }

    public function testMissingNameParameter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/test');

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('fail', $data['status']);
    }

    public function testHtmlResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/test/html');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Привет');
    }
}
