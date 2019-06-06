<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Cycle;

use Cycle\ORM\Promise\Factory as ProxyFactory;
use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Materizalizer\EvalMaterializer;
use Cycle\ORM\PromiseFactoryInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;

final class ProxiesBootloader extends Bootloader implements DependedInterface
{
    public const SINGLETONS = [
        PromiseFactoryInterface::class => ProxyFactory::class,
        MaterializerInterface::class   => EvalMaterializer::class
    ];

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [
            CycleBootloader::class
        ];
    }
}