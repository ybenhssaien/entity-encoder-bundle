<?php

namespace Ybenhssaien\EntityEncoderBundle\Indexer;

use Doctrine\Common\Annotations\Reader;
use Ybenhssaien\EntityEncoderBundle\Exception\BadParamException;
use Ybenhssaien\EntityEncoderBundle\Indexer\Annotation\IndexedBy;
use Ybenhssaien\EntityEncoderBundle\Indexer\Annotation\HasIndexes;
use Ybenhssaien\EntityEncoderBundle\Indexer\Annotation\EntityIndexes;

class EntityLoader
{
    protected Reader $reader;

    protected array $entitiesMap = [];

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function isEntityIndexed($entity): bool
    {
        return \is_array($this->getIndexedEntity($entity));
    }

    /**
     * @return array ["property1" => [class" => "className", "property" => "propertyName"], ...]
     */
    public function getEncryptedProperties($entity): array
    {
        $properties = $this->getIndexedEntity($entity)['properties'] ?? false;

        return $properties ? \array_keys($properties) : [];
    }

    /**
     * @return array ["class" => "className", "property" => "propertyName"]
     */
    public function getPropertyIndexBy($entity, string $property): array
    {
        return $this->getIndexedEntity($entity)['properties'][$property] ?? [];
    }

    public function getAssociationPropertyNameByClassName($entity, $className): ?string
    {
        if (! $entity = $this->getIndexedEntity($entity)) {
            return null;
        }

        return \array_flip($entity['target'])[$className] ?? null;
    }

    /**
     * @param object|string $entity
     *
     * @return array ["target" => [], "properties" => []]
     */
    public function getIndexedEntity($entity)
    {
        if (! is_object($entity) && ! (\is_string($entity) && \class_exists($entity))) {
            throw new BadParamException(sprintf('%s methods accepts an object or a valid class name, [%s] given', __METHOD__, \gettype($entity)));
        }

        $entity = \is_object($entity) ? \get_class($entity) : $entity;

        if (isset($this->entitiesMap[$entity])) {
            return $this->entitiesMap[$entity];
        }

        try {
            $classRef = new \ReflectionClass($entity);

            /** @var HasIndexes $annotation */
            if (! $annotation = $this->reader->getClassAnnotation($classRef, HasIndexes::class)) {
                return [];
            }
        } catch (\ReflectionException $e) {
            return [];
        }

        return $this->entitiesMap[$classRef->getName()] = [
            'target' => $this->getAssociationProperties($classRef->getName()),
            'properties' => $this->getIndexedProperties($classRef),
        ];
    }

    /**
     * @param \ReflectionClass|object|string $entity
     */
    public function getIndexedProperties($entity): array
    {
        $return = [];

        try {
            $classRef = $entity instanceof \ReflectionClass ? $entity : new \ReflectionClass($entity);
        } catch (\ReflectionException $e) {
            return $return;
        }

        foreach ($classRef->getProperties() as $propertyRef) {
            /** @var IndexedBy $annotation */
            if (
            \is_null($annotation = $this->reader->getPropertyAnnotation($propertyRef, IndexedBy::class))
            ) {
                continue;
            }

            $return[$propertyRef->getName()] = [
                'class' => $annotation->class ?: $classRef->getName(),
                'property' => $annotation->property,
            ];
        }

        return $return;
    }

    /**
     * @param \ReflectionClass|object|string $entity
     */
    public function getAssociationProperties($entity): array
    {
        $return = [];

        try {
            $classRef = $entity instanceof \ReflectionClass ? $entity : new \ReflectionClass($entity);
        } catch (\ReflectionException $e) {
            return $return;
        }

        foreach ($classRef->getProperties() as $propertyRef) {
            if (
            \is_null($annotation = $this->reader->getPropertyAnnotation($propertyRef, EntityIndexes::class))
            ) {
                continue;
            }

            $name = $propertyRef->getName();
            $return[$name] = $annotation->class;
        }

        return $return ?: [$classRef->getName()];
    }
}
