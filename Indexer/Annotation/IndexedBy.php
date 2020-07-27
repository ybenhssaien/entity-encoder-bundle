<?php

namespace Ybenhssaien\EntityEncoderBundle\Indexer\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Annotation\Target("PROPERTY")
 * @Annotation\Attributes({
 *     @Annotation\Attribute("class", type = "string"),
 *     @Annotation\Attribute("property", type = "string"),
 * })
 */
class IndexedBy
{
    public ?string $class = null;
    public ?string $property = null;

    public function __construct($attributes = [])
    {
        if (\array_key_exists('class', $attributes)) {
            $this->class = \class_exists($attributes['class']) ? $attributes['class'] : null;
            unset($attributes['class']);
        }

        if (\array_key_exists('property', $attributes)) {
            $this->setProperty($attributes['property']);
            unset($attributes['property']);
        } elseif (\array_key_exists('value', $attributes)) {
            $this->setProperty($attributes['value']);
            unset($attributes['value']);
        }

        if (\is_null($this->property)) {
            throw new AnnotationException(sprintf(
                '"property" is mandatory and cannot be empty, (Ex: @%1$s("property") or @%1$s(property="property"))',
                \get_class()
            ));
        }
    }

    private function setProperty($property)
    {
        $this->property = \is_string($property) ? $property : null;
    }
}
