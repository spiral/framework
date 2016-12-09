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
        'Interop\Container\ContainerInterface'        => ContainerInterface::class,
        'Spiral\Core\InteropContainerInterface'       => ContainerInterface::class,
        'Spiral\Core\FactoryInterface'                => ContainerInterface::class,
        'Spiral\Core\ResolverInterface'               => ContainerInterface::class,

        //Configurator
        'Spiral\Core\ConfiguratorInterface'           => 'Spiral\Core\Configurator',

        //Files
        'Spiral\Files\FilesInterface'                 => 'Spiral\Files\FileManager',

        //Cache component bindings
        'Spiral\Cache\CacheInterface'                 => 'Spiral\Cache\CacheManager',
        'Spiral\Cache\StoreInterface'                 => 'Spiral\Cache\CacheStore',

        //Tokenization and class/invocation location
        'Spiral\Tokenizer\TokenizerInterface'         => 'Spiral\Tokenizer\Tokenizer',
        'Spiral\Tokenizer\ClassLocatorInterface'      => 'Spiral\Tokenizer\ClassLocator',
        'Spiral\Tokenizer\InvocationLocatorInterface' => 'Spiral\Tokenizer\InvocationLocator',

        //Databases
        'Spiral\Database\DatabaseInterface'           => 'Spiral\Database\Entities\Database',
        'Spiral\Database\DatabasesInterface'          => 'Spiral\Database\DatabaseManager',
    ];
}