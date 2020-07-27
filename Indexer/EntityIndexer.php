<?php

namespace Ybenhssaien\EntityEncoderBundle\Indexer;

use Doctrine\Common\Annotations\AnnotationException;

class EntityIndexer
{
    protected EntityLoader $loader;
    protected IndexesGenerator $indexGenerator;

    protected array $target = [];
    protected array $properties = [];

    public function __construct(EntityLoader $loader, IndexesGenerator $indexGenerator)
    {
        $this->loader = $loader;
        $this->indexGenerator = $indexGenerator;
    }

    public function setEncoder(string $encoder): self
    {
        $this->indexGenerator->setEncoder($encoder);

        return $this;
    }

    public function isEntityIndexed($entity): bool
    {
        return $this->loader->isEntityIndexed($entity);
    }

    public function getEncryptedProperties($entity): array
    {
        return $this->loader->getEncryptedProperties($entity);
    }

    public function generateAllIndexes(object $entity): array
    {
        return $this->generatePropertiesIndexes($entity, $this->loader->getEncryptedProperties($entity));
    }

    public function generatePropertiesIndexes(object $entity, array $properties = []): array
    {
        $indexEntities = [];

        foreach ((array) $properties as $property) {
            $indexEntities[] = $this->updatePropertyIndexes($entity, $property);
        }

        return \array_unique($indexEntities, SORT_REGULAR);
    }

    protected function updatePropertyIndexes(object $entity, string $property, ?object $indexEntity = null): ?object
    {
        if (! $indexTarget = $this->loader->getPropertyIndexBy($entity, $property)) {
            return null;
        }

        /* Get the modified value from $entity */
        $value = $this->getPropertyValue($entity, $property);

        /* Regenerate indexes if the is string, otherwise indexes must be empty */
        if (\is_string($value)) {
            $indexesArray = $this->indexGenerator->generateIndexesStartWith($value);
        } else {
            $indexesArray = [];
        }

        /* Check if the target class is an association in the $entity */
        $indexProperty = $this->loader->getAssociationPropertyNameByClassName($entity, $indexTarget['class']);

        if (\is_null($indexEntity)) {
            /* Get $indexEntity from $entity if it is an association */
            if ($indexProperty && \method_exists($entity, $method = 'get'.\ucfirst($indexProperty))) {
                $indexEntity = $entity->$method();
            }

            if (empty($indexEntity)) {
                /* if $indexEntity cannot be found, intantiate the target class */
                if (\get_class($entity) != $indexTarget['class']) {
                    $indexEntity = new $indexTarget['class']();
                } else {
                    /* If the class is not provided, the $idnexEntity is the same as entity */
                    $indexEntity = $entity;
                }
            }
        }

        /* Update indexes in target $indexEntity */
        $this->setPropertyValue($indexEntity, $indexTarget['property'], $indexesArray);

        /* Update association on entity if exists */
        if (\get_class($entity) != $indexTarget['class']) {
            if ($indexProperty) {
                $this->setPropertyValue($entity, $indexProperty, $indexEntity, false);
            }

            /* set Entity on indexEntity if exists */
            $this->setPropertyValue(
                $indexEntity,
                \strtolower((new \ReflectionObject($entity))->getShortName()),
                $entity,
                false
            );
        }

        return $indexEntity;
    }

    protected function getPropertyValue(object $entity, string $property, bool $throwException = true)
    {
        $entityRef = new \ReflectionObject($entity);

        if (($propertyRef = $entityRef->getProperty($property))->isPublic()) {
            return $propertyRef->getValue($entity);
        }

        foreach ($prefixes = ['get', 'is'] as $prefix) {
            $method = $prefix.\ucfirst($property);

            if ($entityRef->hasMethod($method)) {
                return $entityRef->getMethod($method)->invoke($entity);
            }
        }

        if ($throwException) {
            throw new AnnotationException(sprintf('Cannot find methods [%s] in class "%s"', implode(', ', array_map(fn ($prefix) => $prefix.\ucfirst($property), $prefixes)), $entityRef->getName()));
        }

        return null;
    }

    protected function setPropertyValue(object $entity, string $property, $value, bool $throwException = true)
    {
        $entityRef = new \ReflectionObject($entity);

        if ($entityRef->hasProperty($property) && ($propertyRef = $entityRef->getProperty($property))->isPublic()) {
            $propertyRef->setValue($entity, $value);
        } else {
            $method = 'set'.\ucfirst($property);

            if ($entityRef->hasMethod($method)) {
                $entityRef->getMethod($method)->invokeArgs($entity, [$value]);
            } elseif ($throwException) {
                throw new AnnotationException(sprintf('Cannot find "%s::%s()" method', \get_class($entityRef), $method));
            }
        }

        return $entity;
    }
}
