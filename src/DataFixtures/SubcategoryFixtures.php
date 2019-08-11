<?php

namespace App\DataFixtures;

use App\Entity\Subcategory;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class SubcategoryFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i <= 23; $i++) {
            $category = $this->getReference('category-' . mt_rand(0, 4));
            $subcategory = new Subcategory();
            $subcategory->setName($faker->words(3, true));
            $subcategory->setCategory($category);
            $manager->persist($subcategory);
            $this->addReference('subcategory-' . $i, $subcategory);
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
            CategoryFixtures::class
        ];
    }
}
