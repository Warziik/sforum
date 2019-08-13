<?php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController {
    /**
     * @Route("/membres/{slug}.{id}", name="user.profile", requirements={"slug"="^[a-zA-Z0-9-_]+$", "id"="\d+"}, methods={"GET"})
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile(User $user) {
        if (is_null($user)) {
            return $this->redirectToRoute('home');
        }
        return $this->render('user/profile.html.twig', ['user' => $user]);
    }
}
