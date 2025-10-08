<?php

declare(strict_types=1);

namespace Dayploy\DoctrineExtensionsBundle\Cryptable;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class CryptableListener
{
    public function __construct(
        private CryptableService $cryptableService,
    ) {}

    public function prePersist(PrePersistEventArgs $args)
    {
        $entity = $args->getObject();
        $this->cryptableService->encryptValue($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        $this->cryptableService->encryptValue($entity);
    }

    public function postLoad(PostLoadEventArgs $args)
    {
        $entity = $args->getObject();
        $this->cryptableService->decryptValue($entity);
    }

    public function preFlush(PreFlushEventArgs $args)
    {
        /** @var EntityManagerInterface */
        $em = $args->getObjectManager();
        $identityMap = $em->getUnitOfWork()->getIdentityMap();

        foreach ($identityMap as $map) {
            foreach ($map as $entity) {
                $this->cryptableService->encryptValue($entity);
            }
        }
    }
}
