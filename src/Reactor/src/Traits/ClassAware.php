<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\ClassDeclaration;

/**
 * @internal
 */
trait ClassAware
{
    public function addClass(string $name): ClassDeclaration
    {
        return ClassDeclaration::fromElement($this->element->addClass($name));
    }

    public function getClass(string $name): ClassDeclaration
    {
        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        return $this->getClasses()->get(Helpers::extractShortName($name));
    }

    public function getClasses(): Classes
    {
        $classes = \array_filter(
            $this->element->getClasses(),
            static fn (ClassLike $element): bool => $element instanceof ClassType
        );

        return new Classes(\array_map(
            static fn (ClassType $class): ClassDeclaration => ClassDeclaration::fromElement($class),
            $classes
        ));
    }
}
