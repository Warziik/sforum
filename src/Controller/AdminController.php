<?php
namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends EasyAdminController {
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function updateEntity($entity) {
        $this->encodePassword($entity);
        parent::updateEntity($entity);
    }

    public function persistEntity($entity) {
        $this->encodePassword($entity);
        parent::persistEntity($entity);
    }

    public function encodePassword($entity) {
        if (method_exists($entity, 'setPassword')) {
            $entity->setPassword($this->passwordEncoder->encodePassword($entity, $entity->getPassword()));
        }
    }
}
