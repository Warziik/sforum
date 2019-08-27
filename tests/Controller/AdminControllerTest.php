<?php

namespace App\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $userFixtures;

    public function setUp()
    {
        $this->userFixtures = $this->loadFixtures(['App\DataFixtures\UserFixtures'])->getReferenceRepository();
        parent::setUp();
    }

    /**
     * Testing access admin with authorization
     * 
     * @dataProvider getSecureUrls
     */
    public function testAccessAdmin(string $route)
    {
        $user = $this->userFixtures->getReference('user-1');
        $this->loginAs($user, 'main');
        $client = $this->makeClient();

        $url = $this->getUrl($route);
        $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertSame('/admin/?action=list&entity=Category', $client->getResponse()->getTargetUrl());
    }

    /**
     * Testing access admin without being login
     * 
     * @dataProvider getSecureUrls
     */
    public function testSecureUrlsForbidden(string $route)
    {
        $url = $this->getUrl($route);
        $client = $this->makeClient();
        $client->request('GET', $url);

        $this->isSuccessful($client->getResponse(), false);
        $this->assertSame('/connexion', $client->getResponse()->getTargetUrl());
    }

    public function getSecureUrls()
    {
        yield ['easyadmin'];
    }
}
