<?php

namespace App\DataFixtures;

use App\Entity\Topic;
use App\Repository\SubcategoryRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class TopicFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var SubcategoryRepository
     */
    private $subcategoryRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(SubcategoryRepository $subcategoryRepository, UserRepository $userRepository)
    {
        $this->subcategoryRepository = $subcategoryRepository;
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i <= 100; $i++) {
            $subcategory = $this->getReference('subcategory-' . mt_rand(0, 23));
            $user = $this->getReference('user-' . mt_rand(0, 60));
            $topic = new Topic();
            $topic->setTitle($faker->sentence(6));
            $topic->setContent($faker->text);
            $topic->setSubcategory($subcategory);
            $topic->setAuthor($user);
            $manager->persist($topic);
            $this->addReference('topic-' . $i, $topic);
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
            SubcategoryFixtures::class,
            UserFixtures::class
        ];
    }
}
