<?php

namespace App\Tests\Controller\Forum;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;

class TopicReplyControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $topicFixtures;
    private $topicReplyFixtures;
    private $userFixtures;

    public function setUp()
    {
        $this->userFixtures = $this->loadFixtures(['App\DataFixtures\UserFixtures'])->getReferenceRepository();
        $this->topicFixtures = $this->loadFixtures(['App\DataFixtures\TopicFixtures'])->getReferenceRepository();
        $this->topicReplyFixtures = $this->loadFixtures(['App\DataFixtures\TopicresponseFixtures'])->getReferenceRepository();
        parent::setUp();
    }

    public function testCreateTopicReply()
    {
        $topic = $this->topicFixtures->getReference('topic-1');
        $user = $this->userFixtures->getReference('user-1');
        $this->loginAs($user, 'main');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topic_show', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        $crawler = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('newTopicReplyButton')->form();
        $form->setValues(['topic_response[content]' => 'Hello world!']);
        $client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
    }

    public function editTopicReply()
    {
        $topicReply = $this->topicReplyFixtures->getReference('topicreply-1');
        $this->loginAs($topicReply, 'main');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topicresponse_edit', ['id' => $topicReply->getId()]);
        $client->xmlHttpRequest('POST', $url, ['content' => 'My new content!']);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function editTopicReplyWithoutBeingTheAuthor()
    {
        $topicReply = $this->topicReplyFixtures->getReference('topicreply-1');
        $user = $this->userFixtures->getReference('user-1');
        $this->loginAs($user, 'main');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topicresponse_edit', ['id' => $topicReply->getId()]);
        $client->xmlHttpRequest('POST', $url, ['content' => 'My new content!']);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testEditTopicReplyWithoutBeingLogged()
    {
        $topicReply = $this->topicReplyFixtures->getReference('topicreply-1');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topicresponse_edit', ['id' => $topicReply->getId()]);
        $client->xmlHttpRequest('POST', $url, ['content' => 'test']);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('security.login'), $client->getResponse()->getTargetUrl());
    }

    public function testDeleteTopicReplyWithoutBeingTheAuthor()
    {
        $topicReply = $this->topicReplyFixtures->getReference('topicreply-1');
        $user = $this->userFixtures->getReference('user-1');
        $this->loginAs($user, 'main');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topicresponse_delete', ['id' => $topicReply->getId()]);
        $client->request('DELETE', $url);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('home'), $client->getResponse()->getTargetUrl());
    }

    public function testDeleteTopicReplyWithoutBeingLogged()
    {
        $topicReply = $this->topicReplyFixtures->getReference('topicreply-1');
        $client = $this->makeClient();

        $url = $this->getUrl('forum.topicresponse_delete', ['id' => $topicReply->getId()]);
        $client->request('DELETE', $url);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame($this->getUrl('security.login'), $client->getResponse()->getTargetUrl());
    }
}
