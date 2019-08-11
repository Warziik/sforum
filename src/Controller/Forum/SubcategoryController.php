<?php
namespace App\Controller\Forum;

use App\Repository\SubcategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SubcategoryController extends AbstractController {
    /**
     * @var SubcategoryRepository
     */
    private $subcategoryRepository;

    public function __construct(SubcategoryRepository $subcategoryRepository)
    {
        $this->subcategoryRepository = $subcategoryRepository;
    }

    /**
     * @Route("/{slug}-{id}", name="forum.subcategory", methods={"GET"}, requirements={"slug"="^[a-zA-Z0-9-_]+$", "id"="\d+"})
     * @param string $slug
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(string $slug, int $id) {
        $subcategory = $this->subcategoryRepository->find($id);
        if (is_null($subcategory) || $slug != $subcategory->getSlug()) {
            return $this->redirectToRoute('home');
        }
        return $this->render('forum/subcategory.html.twig', ['subcategory' => $subcategory]);
    }
}