<?php

namespace App\Controller\Forum;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\TopicResponse;
use App\Repository\TopicResponseRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TopicresponseController extends AbstractController
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var TopicResponseRepository
     */
    private $topicResponseRepository;

    public function __construct(ObjectManager $em, TopicResponseRepository $topicResponseRepository)
    {
        $this->em = $em;
        $this->topicResponseRepository = $topicResponseRepository;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/sujets/reponses/{id}/edit", name="forum.topicresponse_edit", requirements={"id"="\d+"}, methods={"POST"})
     * @param Request $request
     * @param TopicResponse $topicResponse
     * @param ValidatorInterface $validator
     */
    public function edit(Request $request, TopicResponse $topicResponse, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true);
        if ($this->getUser()->getId() != $topicResponse->getAuthor()->getId()) {
            return $this->json(['message' => "Vous n'êtes pas autorisé à modifier cette réponse."], 403);
        }
        if (!$this->isCsrfTokenValid('edit-topicresponse-' . $topicResponse->getId(), $data['token'])) {
            return $this->json(['message' => "Token CSRF invalide."], 403);
        }

        $content = $data['content'];
        $topicResponse->setContent($content);

        $errors = $validator->validate($topicResponse);
        if (count($errors) > 0) {
            return $this->json(['error' => $errors], 200);
        }

        $this->em->flush();
        return $this->json(['message' => 'Réponse modifiée avec succès.', 'content' => $content], 200);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/sujets/reponses/{id}", name="forum.topicresponse_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     *
     * @param Request $request
     * @param TopicResponse $topicResponse
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, TopicResponse $topicResponse)
    {
        if (
            $this->getUser()->getId() != $topicResponse->getAuthor()->getId() ||
            !$this->isCsrfTokenValid('delete-topicresponse-' . $topicResponse->getId(), $request->get('_token'))
        ) {
            return $this->redirectToRoute('home');
        }

        $this->em->remove($topicResponse);
        $this->em->flush();

        $this->addFlash('success', 'Votre réponse a bien été supprimée.');
        return $this->redirectToRoute('forum.topic_show', ['slug' => $topicResponse->getTopic()->getSlug(), 'id' => $topicResponse->getTopic()->getId()]);
    }
}
