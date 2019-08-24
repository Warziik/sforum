<?php

namespace App\Tests\Controller\Forum;

use App\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TopicControllerTest extends WebTestCase
{
    public function testCreateTopicWithoutBeingLogged()
    {
        $client = static::createClient();
        $client->request('GET', '/sujets/nouveau');

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame('/connexion', $response->getTargetUrl());
    }

    public function testShowTopic()
    {
        $client = static::createClient();
        $topic = $client->getContainer()->get('doctrine')->getRepository(Topic::class)->find(1);
        $crawler = $client->request('GET', sprintf('/sujets/%s.%d', $topic->getSlug(), $topic->getId()));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertContains($topic->getTitle(), $crawler->filter('h1')->text());
    }
}
