<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController {
    /**
     * @Route("/profil/{id}", name="user.profile", requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param int $id
     * @param UserRepository $repository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(int $id, UserRepository $repository) {
        $user = $repository->find($id);
        if (is_null($user)) {
            return $this->redirectToRoute('home');
        }
        return $this->render('user/profile.html.twig', ['user' => $user]);
    }
}