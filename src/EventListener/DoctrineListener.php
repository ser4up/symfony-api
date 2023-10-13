<?php

namespace App\EventListener;

use App\Entity\DateTimeMarkerI;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class DoctrineListener
{
    public function prePersist(PrePersistEventArgs $event)
    {
        $object = $event->getObject();

        if (!$object instanceof DateTimeMarkerI) {
            return;
        }

        $object->setCreatedDate(new \DateTime());
        $object->setUpdatedDate(new \DateTime());
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $object = $event->getObject();

        if (!$object instanceof DateTimeMarkerI) {
            return;
        }

        $object->setUpdatedDate(new \DateTime());
    }
}
