<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Notifications\AccountConfirmationNotification;
use App\Notifications\ForgotPasswordNotification;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
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
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
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
     * @param AccountConfirmationNotification $confirmationNotification
     * @param TokenGeneratorInterface $tokenGenerator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request, AccountConfirmationNotification $confirmationNotification, TokenGeneratorInterface $tokenGenerator): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $user->setAccountConfirmationToken($tokenGenerator->generateToken());

            $this->em->persist($user);
            $this->em->flush();

            $confirmationNotification->notify($user);
            $this->addFlash('success', 'Un mail vient de vous être envoyé afin de valider votre inscription.');
            return $this->redirectToRoute('home');
        }
        return $this->render('security/register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/membres/{slug}.{id}/confirmation/{token}", name="security.accountConfirmation", requirements={"slug"="^[a-zA-Z0-9-_]+$", "id"="\d+", "token"="\w+"})
     * @param Request $request
     * @param string $slug
     * @param int $id
     * @param string $token
     * @param LoginFormAuthenticator $authenticator
     * @param GuardAuthenticatorHandler $authenticatorHandler
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function accountConfirmation(Request $request, string $slug, int $id, string $token, LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $authenticatorHandler) {
        $user = $this->userRepository->findOneBy([
            'id' => $id,
            'slug' => $slug,
            'accountConfirmationToken' => $token
        ]);
        if ($user) {
            $user->setAccountConfirmationToken(null);
            $user->setConfirmed(true);

            $this->em->flush();

            $authenticatorHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main'
            );

            $this->addFlash('success', 'Votre compte a bien été confirmé.');
            return $this->redirectToRoute('user.profile', ['slug' => $user->getSlug(), 'id' => $user->getId()]);
        }
        return $this->redirectToRoute('home');
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
     * @param TokenGeneratorInterface $tokenGenerator
     * @return Response
     */
    public function forgotPassword(Request $request, ForgotPasswordNotification $forgotPasswordNotification, TokenGeneratorInterface $tokenGenerator): Response {
        $form = $this->createFormBuilder(null)
            ->add('email', EmailType::class, ['label' => 'Adresse email'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $findUser = $this->userRepository->findOneBy(['email' => $form->getData(), 'confirmed' => true]);
            if (is_null($findUser)) {
                $this->addFlash("danger", "Cette adresse email n'est liée à aucun compte.");
                return $this->redirectToRoute('security.forgotPassword');
            }
            $findUser->setResetPasswordToken($tokenGenerator->generateToken());
            $this->em->flush();

            $forgotPasswordNotification->notify($findUser);
            $this->addFlash('success', "Un mail vient de vous être envoyé à l'adresse email indiquée.");
            return $this->redirectToRoute('home');
        }
        return $this->render('security/forgotPassword.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/reinitialisation-mot-de-passe/{token}", name="security.resetPassword")
     *
     * @param string $token
     * @param Request $request
     * @param LoginFormAuthenticator $authenticator
     * @param GuardAuthenticatorHandler $authenticatorHandler
     * @return Response
     */
    public function resetPassword(string $token, Request $request, LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $authenticatorHandler): Response {
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

            $authenticatorHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main'
            );

            $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
            return $this->redirectToRoute('user.profile', ['slug' => $user->getSlug(), 'id' => $user->getId()]);
        }
        return $this->render('security/resetPassword.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/deconnexion", name="security.logout")
     */
    public function logout() {
        $this->addFlash('success', 'Vous êtes déconnecté.');
        return $this->redirectToRoute('home');
    }
}
