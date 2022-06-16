<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Framework\Kernel;
use Spiral\Prototype\Bootloader\PrototypeBootloader;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;
use Spiral\Tests\Prototype\Commands\Fixtures\InterfaceResolver;
use Spiral\Tests\Prototype\Commands\Fixtures\ResolvedInterface;

class TestApp extends Kernel
{
    public const LOAD = [
        PrototypeBootloader::class,
    ];

    public function bindApp(): void
    {
        $this->bindWithoutResolver();
        $this->container->bind(Fixtures\ATest3Interface::class, Fixtures\ATest3::class);
        $this->container->bind(ResolvedInterface::class, InterfaceResolver::class);
        $this->container->bind(FilesInterface::class, Files::class);
    }

    public function bindWithoutResolver(): void
    {
        /** @var PrototypeRegistry $registry */
        $registry = $this->container->get(PrototypeRegistry::class);

        $registry->bindProperty('testClass', self::class);
        $registry->bindProperty('test', Fixtures\Test::class);
        $registry->bindProperty('test2', Fixtures\SubFolder\Test::class);
        $registry->bindProperty('test3', Fixtures\ATest3Interface::class);
        $registry->bindProperty('one', InheritedInjection\InjectionOne::class);
        $registry->bindProperty('two', InheritedInjection\InjectionTwo::class);
    }

    public function get(string $target)
    {
        return $this->container->get($target);
    }
}
