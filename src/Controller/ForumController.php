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
     * @Route("/{id<\d+>}", name="forum.subcategory", methods={"GET"})
     * @param int $id
     * @param SubcategoryRepository $subcategoryRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSubCategory(int $id, SubcategoryRepository $subcategoryRepository) {
        $subcategory = $subcategoryRepository->find($id);
        if (is_null($subcategory)) {
            return $this->redirectToRoute('home');
        }
        return $this->render('forum/subcategory.html.twig', ['subcategory' => $subcategory]);
    }
}