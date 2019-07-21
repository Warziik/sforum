<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Notifications\ForgotPasswordNotification;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserRepository $userRepository, ObjectManager $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/inscription", name="security.register")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function register(Request $request): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Votre compte a bien été crée.');
            return $this->redirectToRoute('security.login');
        }
        return $this->render('security/register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/connexion", name="security.login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response {
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername()
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/mot-de-passe-oublie", name="security.forgotPassword")
     *
     * @param Request $request
     * @param ForgotPasswordNotification $forgotPasswordNotification
     * @return Response
     */
    public function forgotPassword(Request $request, ForgotPasswordNotification $forgotPasswordNotification): Response {
        $form = $this->createFormBuilder(null)
            ->add('email', EmailType::class, ['label' => 'Adresse email'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $findUser = $this->userRepository->findOneBy(['email' => $form->getData()]);
            if (is_null($findUser)) {
                $this->addFlash("danger", "Cette adresse email n'est liée à aucun compte.");
                return $this->redirectToRoute('security.forgotPassword');
            }
            $findUser->setResetPasswordToken(substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 60));
            $this->em->flush();

            $forgotPasswordNotification->notify($findUser);
            $this->addFlash('success', "Un mail vient de vous être envoyé à l'adresse email indiquée.");
            return $this->redirectToRoute('home');
        }
        return $this->render('security/forgotPassword.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/reinitialisation-mot-de-passe/{token}", name="security.resetPassword", requirements={"token"="\w+"})
     *
     * @param string $token
     * @param Request $request
     * @return Response
     */
    public function resetPassword(string $token, Request $request): Response {
        $user = $this->userRepository->findOneBy(['resetPasswordToken' => $token]);
        if(is_null($user)) {
            $this->addFlash('danger', 'Token invalide.');
            return $this->redirectToRoute('home');
        }
        $form = $this->createFormBuilder($user)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Nouveua mot de passe'],
                'second_options' => ['label' => 'Confirmez votre nouveua mot de passe']
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $user->setResetPasswordToken(null);
            $this->em->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
            return $this->redirectToRoute('security.login');
        }
        return $this->render('security/resetPassword.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/deconnexion", name="security.logout")
     * @throws \Exception
     */
    public function logout() {
        throw new \Exception('Access denied.');
    }
}