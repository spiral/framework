<?php

/**
 * Spiral Framework.
 *
 * @author Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Fixture;

use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;

class DependedElement extends ClassDeclaration implements DependedInterface
{
    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [DependedInterface::class => 'DependencyAlias'];
    }
}
