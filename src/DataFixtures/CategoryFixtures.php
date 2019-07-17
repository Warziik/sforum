<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 0; $i < 4; $i++) {
            $category = new Category();
            $category->setName('Catégorie n°' . $i);
            $manager->persist($category);
        }
        $manager->flush();
    }
}
