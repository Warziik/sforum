<?php

namespace App\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $userFixtures;

    public function setUp()
    {
        $this->userFixtures = $this->loadFixtures(['App\DataFixtures\UserFixtures'])->getReferenceRepository();
        parent::setUp();
    }

    public function testAccessProfile()
    {
        $user = $this->userFixtures->getReference('user-1');

        $url = $this->getUrl('user.profile', ['slug' => $user->getSlug(), 'id' => $user->getId()]);

        $client = $this->makeClient();
        $client->request('GET', $url);

        $this->isSuccessful($client->getResponse());
    }
}
