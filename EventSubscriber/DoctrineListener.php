<?php

namespace Ybenhssaien\EntityEncoderBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Ybenhssaien\EntityEncoderBundle\Indexer\EntityIndexer;
use Ybenhssaien\EntityEncoderBundle\Encoder\SearchableEncoder;

class DoctrineListener implements EventSubscriber
{
    protected EntityIndexer $indexer;

    public function __construct(EntityIndexer $indexer)
    {
        $this->indexer = $indexer->setEncoder(SearchableEncoder::class);
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($this->indexer->isEntityIndexed($entity)) {
            $entityManager = $event->getObjectManager();

            $indexEntities = $this->indexer->generateAllIndexes($entity);

            if ($indexEntities) {
                foreach ($indexEntities as $indexEntity) {
                    $entityManager->persist($indexEntity);
                }

                $entityManager->flush();
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($this->indexer->isEntityIndexed($entity)) {
            /**
             * getEntityChangeSet() : returns an array of old and new value for updates.
             *
             * Example : ['name' => ['old name', 'new name'], ...]
             */
            $updatedData = array_map(
                fn ($value) => \is_array($value) ? \end($value) : $value,
                $event->getObjectManager()->getUnitOfWork()->getEntityChangeSet($entity)
            );

            /* Get only encrypted data */
            if ($changed = \array_intersect(
                $this->indexer->getEncryptedProperties(),
                \array_keys($updatedData)
            )) {
                $entityManager = $event->getObjectManager();

                $indexEntities = $this->indexer->generatePropertiesIndexes($entity, $changed);

                if ($indexEntities) {
                    foreach ($indexEntities as $indexEntity) {
                        $entityManager->persist($indexEntity);
                    }

                    $entityManager->flush();
                }
            }
        }
    }
}
