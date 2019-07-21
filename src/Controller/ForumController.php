<?php
namespace App\Controller;

use App\Entity\Topic;
use App\Entity\TopicResponse;
use App\Form\TopicResponseType;
use App\Form\TopicType;
use App\Repository\CategoryRepository;
use App\Repository\SubcategoryRepository;
use App\Repository\TopicRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ForumController extends AbstractController {
    /**
     * @var TopicRepository
     */
    private $topicRepository;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(TopicRepository $topicRepository, ObjectManager $em)
    {
        $this->topicRepository = $topicRepository;
        $this->em = $em;
    }

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

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/sujets/nouveau", name="forum.topic_new")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newTopic(Request $request) {
        $topic = new Topic();
        $topic->setAuthor($this->getUser());

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($topic);
            $this->em->flush();

            $this->addFlash('success', 'Votre sujet a bien été crée.');
            return $this->redirectToRoute('forum.topic_show', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        }
        return $this->render('forum/topic/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/sujets/{slug}-{id}", name="forum.topic_show", requirements={"slug"="^[a-zA-Z0-9-_]+$", "id"="\d+"}, methods={"GET", "POST"})
     *
     * @param string $slug
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTopic(string $slug, int $id,  Request $request) {
        $topic = $this->topicRepository->find($id);
        if (is_null($topic) || $slug != $topic->getSlug()) {
            return $this->redirectToRoute('home');
        }
        $topicResponse = new TopicResponse();
        $topicResponse->setAuthor($this->getUser());
        $topicResponse->setTopic($topic);
        $form = $this->createForm(TopicResponseType::class, $topicResponse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($topicResponse);
            $this->em->flush();

            $this->addFlash('success', 'Votre réponse a bien été postée.');
            return $this->redirectToRoute('forum.topic_show', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        }
        return $this->render('forum/topic/show.html.twig', ['topic' => $topic, 'form' => $form->createView()]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/sujets/{slug}-{id}/edition", name="forum.topic_edit", requirements={"slug"="^[a-zA-Z0-9-_]+$", "id"="\d+"}, methods={"GET", "POST"})
     *
     * @param Request $request
     * @param string $slug
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editTopic(Request $request, string $slug, int $id) {
        $topic = $this->topicRepository->find($id);
        if (is_null($topic) || $slug != $topic->getSlug() || $this->getUser()->getId() != $topic->getAuthor()->getId()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Votre sujet a bien été modifié.');
            return $this->redirectToRoute('forum.topic_show', ['slug' => $topic->getSlug(), 'id' => $topic->getId()]);
        }
        return $this->render('forum/topic/edit.html.twig', ['topic' => $topic, 'form' => $form->createView()]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/sujets/{slug}-{id}", name="forum.topic_delete", requirements={"slug"="^[a-zA-Z0-9-_]+$", "id"="\d+"}, methods={"DELETE"})
     *
     * @param string $slug
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(string $slug, int $id, Request $request) {
        $topic = $this->topicRepository->find($id);
        if (
            $this->getUser()->getId() != $topic->getAuthor()->getId() ||
            !$this->isCsrfTokenValid('delete-topic-' . $topic->getId(), $request->get('_token'))
        ) {
            return $this->redirectToRoute('home');
        }

        $this->em->remove($topic);
        $this->em->flush();

        $this->addFlash('success', 'Votre sujet a bien été supprimé.');
        return $this->redirectToRoute('home');
    }
}