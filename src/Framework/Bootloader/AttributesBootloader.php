<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Attributes\Manager;
use Spiral\Attributes\ManagerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Container;
use Spiral\Boot\Bootloader\Bootloader;

final class AttributesBootloader extends Bootloader
{
    /**
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        $container->bindSingleton(ManagerInterface::class, Manager::class);
        $container->bindSingleton(Manager::class, Manager::class);

        $container->bindSingleton(ReaderInterface::class, static function (ManagerInterface $manager) {
            return $manager->get();
        });
    }
}
