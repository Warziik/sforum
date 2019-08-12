<?php
namespace App\Event;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageCacheSubscriber implements EventSubscriber {
    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct(UploaderHelper $uploaderHelper, CacheManager $cacheManager)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return ['preUpdate', 'preRemove'];
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args) {
        $entity = $args->getEntity();
        if (!$entity instanceof User) {
            return;
        }
        if ($entity->getImageFile() instanceof UploadedFile) {
            $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args) {
        $entity = $args->getEntity();
        if (!$entity instanceof User) {
            return;
        }
        $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
    }
}
