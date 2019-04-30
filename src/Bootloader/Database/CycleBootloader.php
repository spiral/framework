<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Database;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Bootloader\TokenizerBootloader;
use Spiral\Core\Container\SingletonInterface;


final class CycleBootloader extends Bootloader implements DependedInterface, SingletonInterface
{
    // todo: init cycle

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [TokenizerBootloader::class, DatabaseBootloader::class];
    }
}