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
        'Interop\Container\ContainerInterface'              => ContainerInterface::class,
        'Spiral\Core\InteropContainerInterface'             => ContainerInterface::class,
        'Spiral\Core\FactoryInterface'                      => ContainerInterface::class,
        'Spiral\Core\ResolverInterface'                     => ContainerInterface::class,

        //Files
        'Spiral\Files\FilesInterface'                       => 'Spiral\Files\FileManager',

        //Instrumental bindings
        'Psr\Log\LoggerInterface'                           => 'Spiral\Debug\SharedLogger',
        'Spiral\Debug\LogsInterface'                        => 'Spiral\Debug\Debugger',
        'Spiral\Debug\SnapshotInterface'                    => 'Spiral\Debug\Snapshot',
        'Spiral\Encrypter\EncrypterInterface'               => 'Spiral\Encrypter\Encrypter',

        //Cache component bindings
        'Spiral\Cache\CacheInterface'                       => 'Spiral\Cache\CacheManager',
        'Spiral\Cache\StoreInterface'                       => 'Spiral\Cache\CacheStore',

        //Views
        'Spiral\Views\ViewsInterface'                       => 'Spiral\Views\ViewManager',

        //Storage manager interfaces
        'Spiral\Storage\StorageInterface'                   => 'Spiral\Storage\StorageManager',
        'Spiral\Storage\BucketInterface'                    => 'Spiral\Storage\Entities\StorageBucket',
        'Spiral\Session\SessionInterface'                   => 'Spiral\Session\SessionStore',

        //Tokenization and class/invocation location
        'Spiral\Tokenizer\TokenizerInterface'               => 'Spiral\Tokenizer\Tokenizer',
        'Spiral\Tokenizer\ClassLocatorInterface'            => 'Spiral\Tokenizer\ClassLocator',
        'Spiral\Tokenizer\InvocationLocatorInterface'       => 'Spiral\Tokenizer\InvocationLocator',

        //Validation and translation
        'Spiral\Validation\ValidatorInterface'              => 'Spiral\Validation\Validator',
        'Symfony\Component\Translation\TranslatorInterface' => 'Spiral\Translator\TranslatorInterface',
        'Spiral\Translator\TranslatorInterface'             => 'Spiral\Translator\Translator',
        'Spiral\Translator\SourceInterface'                 => 'Spiral\Translator\TranslationSource',

        //Databases
        'Spiral\Database\DatabaseInterface'                 => 'Spiral\Database\Entities\Database',
        'Spiral\Database\DatabasesInterface'                => 'Spiral\Database\DatabaseManager',

        //Http
        'Spiral\Http\HttpInterface'                         => 'Spiral\Http\HttpDispatcher',
        'Spiral\Http\InputInterface'                        => 'Spiral\Http\Input\InputManager',

        //Modules
        'Spiral\Modules\PublisherInterface'                 => 'Spiral\Modules\Entities\Publisher',
        'Spiral\Modules\RegistratorInterface'               => 'Spiral\Modules\Entities\Registrator',
    ];
}