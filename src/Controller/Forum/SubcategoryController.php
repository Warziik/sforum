<?php
namespace App\Controller\Forum;

use App\Repository\SubcategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(string $slug, int $id, Request $request, PaginatorInterface $paginator) {
        $subcategory = $this->subcategoryRepository->find($id);
        if (is_null($subcategory) || $slug != $subcategory->getSlug()) {
            return $this->redirectToRoute('home');
        }

        $pagination = $paginator->paginate(
            $subcategory->getTopics(),
            $request->query->getInt('page', 1),
            12,
        );
        return $this->render('forum/subcategory.html.twig', compact('subcategory', 'pagination'));
    }
}
