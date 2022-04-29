<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation as MarkerInterface;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor as MarkerAnnotation;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Attributes\ReaderInterface;

final class Facade implements InstantiatorInterface
{
    private readonly DoctrineInstantiator $doctrine;
    private readonly NamedArgumentsInstantiator $named;
    private readonly ReaderInterface $reader;

    public function __construct(ReaderInterface $reader = null)
    {
        $this->reader = $reader ?? new AttributeReader($this);
        $this->doctrine = new DoctrineInstantiator();
        $this->named = new NamedArgumentsInstantiator();
    }

    public function instantiate(\ReflectionClass $attr, array $arguments, \Reflector $context = null): object
    {
        if ($this->isNamedArguments($attr)) {
            return $this->named->instantiate($attr, $arguments, $context);
        }

        return $this->doctrine->instantiate($attr, $arguments, $context);
    }

    private function isNamedArguments(\ReflectionClass $class): bool
    {
        return $this->providesNamedArgumentsInterface($class) ||
            $this->providesNamedArgumentsAttribute($class)
        ;
    }

    private function providesNamedArgumentsAttribute(\ReflectionClass $class): bool
    {
        return \class_exists(MarkerAnnotation::class)
            ? $this->reader->firstClassMetadata($class, MarkerAnnotation::class) !== null
            : $this->reader->firstClassMetadata($class, NamedArgumentConstructor::class) !== null;
    }

    private function providesNamedArgumentsInterface(\ReflectionClass $class): bool
    {
        return \is_subclass_of($class->getName(), MarkerInterface::class);
    }
}
