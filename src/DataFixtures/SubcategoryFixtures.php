<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Subcategory;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SubcategoryFixtures extends Fixture
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
        for ($i = 1; $i < 10; $i++) {
            $c = $this->categoryRepository->find(19);

            $subcategory = new Subcategory();
            $subcategory->setName('Sous-catégorie n°' . $i);
            $subcategory->setCategory($c);

            $manager->persist($subcategory);
        }
        $manager->flush();
    }
}
