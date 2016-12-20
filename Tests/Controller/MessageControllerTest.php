<?php

namespace Hush\ChatBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessageControllerTest extends WebTestCase
{
    public function testUnreadedmessages()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/messages/unreaded');
    }

    public function testDialogs()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/messages/dialogs');
    }

    public function testDialogmessages()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/messages/dialogs/{id}');
    }

    public function testSendmessage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/messages');
    }

    public function testReadmessage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/messages/{id}/read');
    }

    public function testGetmessage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/messages/{id}');
    }

}
