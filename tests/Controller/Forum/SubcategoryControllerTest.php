<?php

namespace App\Tests\Controller\Forum;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class SubcategoryControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $fixtures;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(['App\DataFixtures\SubcategoryFixtures'])->getReferenceRepository();
    }

    public function testShowSubcategory()
    {
        $subcategory = $this->fixtures->getReference('subcategory-1');
        $client = $this->makeClient();
        $crawler = $client->request('GET', sprintf('/%s.%d', $subcategory->getSlug(), $subcategory->getId()));

        $this->isSuccessful($client->getResponse());
        $this->assertContains($subcategory->getName(), $crawler->filter('h1')->text());
    }
}
