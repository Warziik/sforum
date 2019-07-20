<?php
namespace App\Notifications;

use App\Entity\User;
use Twig\Environment;

class ForgotPasswordNotification {
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    /**
     * ForgotPasswordNotification constructor.
     * @param \Swift_Mailer $mailer
     * @param Environment $renderer
     */
    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    {

        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    /**
     * @param User $user
     */
    public function notify(User $user): void {
        try {
            $message = (new \Swift_Message('Réinitialisation de mot de passe - SForum'))
                ->setFrom('noreply@sforum.com')
                ->setTo($user->getEmail())
                ->setBody($this->renderer->render('emails/forgotpassword.html.twig', ['user' => $user]), 'text/html');
            $this->mailer->send($message);
        } catch (\Exception $e) {
            echo "Une erreur s'est produite lors de l'envoi de l'email. (debug: " . $e->getMessage() . ")";
        }
    }
}