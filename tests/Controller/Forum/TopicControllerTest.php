<?php

namespace App\Tests\Controller\Forum;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class TopicControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $topicFixtures;
    private $userFixtures;

    public function setUp()
    {
        $this->userFixtures = $this->loadFixtures(['App\DataFixtures\UserFixtures'])->getReferenceRepository();
        $this->topicFixtures = $this->loadFixtures(['App\DataFixtures\TopicFixtures'])->getReferenceRepository();
    }

    public function testCreateTopic()
    {
        $user = $this->userFixtures->getReference('user-1');
        $this->loginAs($user, 'main');
        $client = $this->makeClient();

        $crawler = $client->request('GET', $this->getUrl('forum.topic_new'));
        $this->isSuccessful($client->getResponse());

        $form = $crawler->selectButton('newTopicButton')->form();
        $form->setValues([
            'topic[title]' => 'My new topic!',
            'topic[subcategory]' => '1',
            'topic[content]' => 'Hello everyone, today...'
        ]);
        $client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testCreateTopicWithoutBeingLogged()
    {
        $client = $this->makeClient();
        $client->request('GET', $this->getUrl('forum.topic_new'));

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('security.login'), $client->getResponse()->getTargetUrl());
    }

    public function testEditTopic()
    {
        $topic = $this->topicFixtures->getReference('topic-1');
        $this->loginAs($topic->getAuthor(), 'main');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topic_edit', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        $crawler = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertContains($topic->getTitle(), $crawler->filter('h1')->text());

        $form = $crawler->selectButton('editTopicButton')->form();
        $form->setValues([
            'topic[title]' => 'My new topic updated!',
            'topic[subcategory]' => '3',
            'topic[content]' => 'Hello world!'
        ]);
        $client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('forum.topic_show', ['slug' => 'my-new-topic-updated', 'id' => $topic->getId()]), $client->getResponse()->getTargetUrl());
    }

    public function testEditTopicWithoutBeingTheAuthor()
    {
        $topic = $this->topicFixtures->getReference('topic-1');
        $user = $this->userFixtures->getReference('user-1');
        $this->loginAs($user, 'main');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topic_edit', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('home'), $client->getResponse()->getTargetUrl());
    }

    public function testEditTopicWithoutBeingLogged()
    {
        $topic = $this->topicFixtures->getReference('topic-1');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topic_edit', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('security.login'), $client->getResponse()->getTargetUrl());
    }

    public function testDeleteTopic()
    {
        $topic = $this->topicFixtures->getReference('topic-1');
        $this->loginAs($topic->getAuthor(), 'main');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topic_delete', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        $client->request('DELETE', $url);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('home'), $client->getResponse()->getTargetUrl());
    }

    public function testDeleteTopicWithoutBeingTheAuthor()
    {
        $topic = $this->topicFixtures->getReference('topic-1');
        $user = $this->userFixtures->getReference('user-1');
        $this->loginAs($user, 'main');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topic_delete', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        $client->request('DELETE', $url);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('home'), $client->getResponse()->getTargetUrl());
    }

    public function testDeleteTopicWithoutBeingLogged()
    {
        $topic = $this->topicFixtures->getReference('topic-1');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topic_delete', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        $client->request('DELETE', $url);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('security.login'), $client->getResponse()->getTargetUrl());
    }

    public function testShowTopic()
    {
        $topic = $this->topicFixtures->getReference('topic-1');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topic_show', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        $crawler = $client->request('GET', $url);

        $this->isSuccessful($client->getResponse());
        $this->assertContains($topic->getTitle(), $crawler->filter('h1')->text());
    }
}
