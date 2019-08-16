<?php

namespace App\Controller\Forum;

use App\Entity\Subcategory;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubcategoryController extends AbstractController
{

    /**
     * @Route("/{slug}.{id}", name="forum.subcategory", methods={"GET"}, requirements={"slug"="^[a-zA-Z0-9-_]+$"})
     * @param Subcategory $subcategory
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Subcategory $subcategory, Request $request, PaginatorInterface $paginator)
    {
        $pagination = $paginator->paginate(
            $subcategory->getTopics(),
            $request->query->getInt('page', 1),
            12,
        );
        return $this->render('forum/subcategory.html.twig', compact('subcategory', 'pagination'));
    }
}
