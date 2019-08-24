<?php

namespace App\Tests\Controller\Forum;

use App\Entity\Subcategory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SubcategoryControllerTest extends WebTestCase
{
    public function testShowSubcategory()
    {
        $client = static::createClient();
        $subcategory = $client->getContainer()->get('doctrine')->getRepository(Subcategory::class)->find(1);
        $crawler = $client->request('GET', sprintf('/%s.%d', $subcategory->getSlug(), $subcategory->getId()));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertContains($subcategory->getName(), $crawler->filter('h1')->text());
    }
}
