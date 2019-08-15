<?php

namespace App\DataFixtures;

use App\Entity\TopicResponse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class TopicresponseFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i <= 450; $i++) {
            $topic = $this->getReference('topic-' . mt_rand(0, 342));
            $user = $this->getReference('user-' . mt_rand(0, 60));
            $topicresponse = new TopicResponse();
            $topicresponse->setContent($faker->sentences(5, true));
            $topicresponse->setCreatedAt($faker->dateTimeThisDecade);
            $topicresponse->setTopic($topic);
            $topicresponse->setAuthor($user);

            $manager->persist($topicresponse);
        }
        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
            TopicFixtures::class
        ];
    }
}
