<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\EnumDeclaration;
use Spiral\Reactor\InterfaceDeclaration;
use Spiral\Reactor\TraitDeclaration;

/**
 * Classes, interfaces, traits, enums aggregation.
 *
 * @extends Aggregator<ClassDeclaration|InterfaceDeclaration|TraitDeclaration|EnumDeclaration>
 */
final class Elements extends Aggregator
{
    public function __construct(array $elements)
    {
        parent::__construct([
            ClassDeclaration::class,
            InterfaceDeclaration::class,
            TraitDeclaration::class,
            EnumDeclaration::class,
        ], $elements);
    }
}
