<?php

namespace App\Controller\Forum;

use App\Entity\Topic;
use App\Entity\TopicResponse;
use App\Form\TopicResponseType;
use App\Form\TopicType;
use App\Repository\TopicRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TopicController extends AbstractController
{
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
     * @IsGranted("ROLE_USER")
     * @Route("/sujets/nouveau", name="forum.topic_new")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {
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
     * @Route("/sujets/{slug}.{id}", name="forum.topic_show", requirements={"slug"="^[a-zA-Z0-9-_]+$", "id"="\d+"}, methods={"GET", "POST"})
     *
     * @param Topic $topic
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Topic $topic,  Request $request)
    {
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
     * @param Topic $topic
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function edit(Request $request, Topic $topic)
    {
        if ($this->getUser()->getId() != $topic->getAuthor()->getId()) {
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
     * @param Request $request
     * @param Topic $topic
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, Topic $topic)
    {
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
