<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Annotations;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 */
final class AnnotatedMethod
{
    private \ReflectionMethod $method;

    /** @var mixed */
    private $annotation;

    /**
     * @param mixed             $annotation
     */
    public function __construct(\ReflectionMethod $method, $annotation)
    {
        $this->method = $method;
        $this->annotation = $annotation;
    }

    public function getClass(): \ReflectionClass
    {
        return $this->method->getDeclaringClass();
    }

    public function getMethod(): \ReflectionMethod
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }
}
