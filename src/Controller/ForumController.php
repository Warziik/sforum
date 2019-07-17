<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\SubcategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ForumController extends AbstractController {
    /**
     * @Route("/", name="home", methods={"GET"})
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(CategoryRepository $categoryRepository) {
        return $this->render('index.html.twig', ['categories' => $categoryRepository->findAll()]);
    }

    /**
     * @Route("/{slug}-{id}", name="forum.subcategory", methods={"GET"}, requirements={"slug"="^[a-zA-Z0-9-_]+$", "id"="\d+"})
     * @param string $slug
     * @param int $id
     * @param SubcategoryRepository $subcategoryRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSubCategory(string $slug, int $id, SubcategoryRepository $subcategoryRepository) {
        $subcategory = $subcategoryRepository->find($id);
        if (is_null($subcategory) || $slug != $subcategory->getSlug()) {
            return $this->redirectToRoute('home');
        }
        return $this->render('forum/subcategory.html.twig', ['subcategory' => $subcategory]);
    }
}