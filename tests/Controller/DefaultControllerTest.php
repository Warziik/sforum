<?php

namespace App\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @dataProvider getPublicUrls
     */
    public function testPublicUrls(string $route)
    {
        $url = $this->getUrl($route);
        $client = $this->makeClient();
        $client->request('GET', $url);

        $this->isSuccessful($client->getResponse());
    }

    public function getPublicUrls()
    {
        yield ['home'];
        yield ['security.login'];
        yield ['security.forgotPassword'];
        yield ['security.register'];
    }
}
