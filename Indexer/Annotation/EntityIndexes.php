<?php

namespace Ybenhssaien\EntityEncoderBundle\Indexer\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Annotation\Target("PROPERTY")
 * @Annotation\Attributes({
 *     @Annotation\Attribute("class", type = "string"),
 * })
 */
class EntityIndexes
{
    public ?string $class = null;

    public function __construct($attributes = [])
    {
        if (\array_key_exists('class', $attributes)) {
            $this->setClass($attributes['class']);
            unset($attributes['class']);
        } elseif (\array_key_exists('value', $attributes)) {
            $this->setClass($attributes['value']);
            unset($attributes['value']);
        }

        if (\is_null($this->class)) {
            throw new AnnotationException(sprintf('"class" is mandatory and cannot be empty  (Ex: @%s(class="Property"))', \get_class()));
        }
    }

    private function setClass($class)
    {
        $this->class = \class_exists($class) ? $class : null;
    }
}
