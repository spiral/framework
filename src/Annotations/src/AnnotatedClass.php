<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Annotations;

final class AnnotatedClass
{
    /** @var \ReflectionClass */
    private $class;

    /** @var mixed */
    private $annotation;

    /**
     * @param \ReflectionClass $class
     * @param mixed            $annotation
     */
    public function __construct(\ReflectionClass $class, $annotation)
    {
        $this->class = $class;
        $this->annotation = $annotation;
    }

    /**
     * @return \ReflectionClass
     */
    public function getClass(): \ReflectionClass
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }
}
