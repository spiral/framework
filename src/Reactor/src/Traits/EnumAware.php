<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\Helpers;
use Spiral\Reactor\Aggregator\Enums;
use Spiral\Reactor\EnumDeclaration;

/**
 * @internal
 */
trait EnumAware
{
    public function addEnum(string $name): EnumDeclaration
    {
        return EnumDeclaration::fromElement($this->element->addEnum($name));
    }

    public function getEnum(string $name): EnumDeclaration
    {
        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        return $this->getEnums()->get(Helpers::extractShortName($name));
    }

    public function getEnums(): Enums
    {
        $enums = \array_filter(
            $this->element->getClasses(),
            static fn (ClassLike $element): bool => $element instanceof EnumType
        );

        return new Enums(\array_map(
            static fn (EnumType $enum): EnumDeclaration => EnumDeclaration::fromElement($enum),
            $enums
        ));
    }
}
