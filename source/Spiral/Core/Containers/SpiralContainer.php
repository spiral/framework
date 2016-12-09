<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Containers;

use Spiral\Core\Container;
use Spiral\Core\ContainerInterface;

/**
 * Default spiral container with pre-defined set of core bindings (since this is default container
 * singleton flags are not forced).
 */
class SpiralContainer extends Container implements ContainerInterface
{
    /**
     * {@inheritdoc}
     *
     * @invisible
     */
    protected $bindings = [
        //Container related bindings
        'Interop\Container\ContainerInterface'  => ContainerInterface::class,
        'Spiral\Core\InteropContainerInterface' => ContainerInterface::class,
        'Spiral\Core\FactoryInterface'          => ContainerInterface::class,
        'Spiral\Core\ResolverInterface'         => ContainerInterface::class,

        //Configurator
        'Spiral\Core\ConfiguratorInterface'                 => 'Spiral\Core\Configurator',

        //Files
        'Spiral\Files\FilesInterface'           => 'Spiral\Files\FileManager',
    ];
}