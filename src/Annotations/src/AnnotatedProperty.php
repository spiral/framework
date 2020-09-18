<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Annotations;

final class AnnotatedProperty
{
    /** @var \ReflectionProperty */
    private $property;

    /** @var mixed */
    private $annotation;

    /**
     * @param \ReflectionProperty $property
     * @param mixed               $annotation
     */
    public function __construct(\ReflectionProperty $property, $annotation)
    {
        $this->property = $property;
        $this->annotation = $annotation;
    }

    /**
     * @return \ReflectionClass
     */
    public function getClass(): \ReflectionClass
    {
        return $this->property->getDeclaringClass();
    }

    /**
     * @return \ReflectionProperty
     */
    public function getProperty(): \ReflectionProperty
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }
}
