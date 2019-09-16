<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Cycle;

use Cycle\ORM\Promise\MaterializerInterface;
use Cycle\ORM\Promise\Materizalizer\EvalMaterializer;
use Cycle\ORM\Promise\ProxyFactory;
use Cycle\ORM\PromiseFactoryInterface;
use Spiral\Boot\Bootloader\Bootloader;

final class ProxiesBootloader extends Bootloader
{
    const DEPENDENCIES = [
        CycleBootloader::class
    ];

    public const SINGLETONS = [
        PromiseFactoryInterface::class => ProxyFactory::class,
        MaterializerInterface::class   => EvalMaterializer::class
    ];
}
