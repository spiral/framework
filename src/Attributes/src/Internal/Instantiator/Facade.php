<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation as MarkerInterface;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor as MarkerAnnotation;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Attributes\ReaderInterface;

final class Facade implements InstantiatorInterface
{
    /**
     * @var DoctrineInstantiator
     */
    private $doctrine;

    /**
     * @var NamedArgumentsInstantiator
     */
    private $named;

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @param ReaderInterface|null $reader
     */
    public function __construct(ReaderInterface $reader = null)
    {
        $this->reader = $reader ?? new AttributeReader($this);
        $this->doctrine = new DoctrineInstantiator();
        $this->named = new NamedArgumentsInstantiator();
    }

    /**
     * {@inheritDoc}
     */
    public function instantiate(\ReflectionClass $attr, array $arguments, \Reflector $context = null): object
    {
        if ($this->isNamedArguments($attr)) {
            return $this->named->instantiate($attr, $arguments, $context);
        }

        return $this->doctrine->instantiate($attr, $arguments, $context);
    }

    /**
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isNamedArguments(\ReflectionClass $class): bool
    {
        return $this->providesNamedArgumentsInterface($class) ||
            $this->providesNamedArgumentsAttribute($class)
        ;
    }

    /**
     * @param \ReflectionClass $class
     * @return bool
     */
    private function providesNamedArgumentsAttribute(\ReflectionClass $class): bool
    {
        if (\class_exists(MarkerAnnotation::class)) {
            return $this->reader->firstClassMetadata($class, MarkerAnnotation::class) !== null;
        }

        return $this->reader->firstClassMetadata($class, NamedArgumentConstructor::class);
    }

    /**
     * @param \ReflectionClass $class
     * @return bool
     */
    private function providesNamedArgumentsInterface(\ReflectionClass $class): bool
    {
        return \is_subclass_of($class->getName(), MarkerInterface::class);
    }
}
