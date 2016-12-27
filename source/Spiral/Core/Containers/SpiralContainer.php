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

        //Logging and debugging
        'Spiral\Debug\LogsInterface'                        => 'Spiral\Debug\LogManager',

        //Configurator
        'Spiral\Core\ConfiguratorInterface'                 => 'Spiral\Core\Configurator',

        //Files
        'Spiral\Files\FilesInterface'                       => 'Spiral\Files\FileManager',

        //Encrypter
        'Spiral\Encrypter\EncrypterInterface'               => 'Spiral\Encrypter\Encrypter',

        //Tokenization and class/invocation location
        'Spiral\Tokenizer\TokenizerInterface'               => 'Spiral\Tokenizer\Tokenizer',
        'Spiral\Tokenizer\ClassesInterface'                 => 'Spiral\Tokenizer\ClassLocator',
        'Spiral\Tokenizer\InvocationsInterface'             => 'Spiral\Tokenizer\InvocationsLocator',

        //Databases
        'Spiral\Database\DatabaseInterface'                 => 'Spiral\Database\Entities\Database',
        'Spiral\Database\DatabasesInterface'                => 'Spiral\Database\DatabaseManager',

        //Http
        'Spiral\Http\HttpInterface'                         => 'Spiral\Http\HttpDispatcher',
        'Spiral\Http\Request\InputInterface'                => 'Spiral\Http\Request\InputManager',

        //Http based pagination
        'Spiral\Pagination\PaginatorsInterface'             => 'Spiral\Pagination\PaginationManager',

        //Storage manager interfaces
        'Spiral\Storage\StorageInterface'                   => 'Spiral\Storage\StorageManager',
        'Spiral\Storage\BucketInterface'                    => 'Spiral\Storage\Entities\StorageBucket',
        'Spiral\Session\SessionInterface'                   => 'Spiral\Session\SessionStore',

        //Default validator
        'Spiral\Validation\ValidatorInterface'              => 'Spiral\Validation\Validator',

        //Translations and internalization
        'Symfony\Component\Translation\TranslatorInterface' => 'Spiral\Translator\TranslatorInterface',
        'Spiral\Translator\TranslatorInterface'             => 'Spiral\Translator\Translator',
        'Spiral\Translator\SourceInterface'                 => 'Spiral\Translator\TranslationSource',

        //Views
        'Spiral\Views\ViewsInterface'                       => 'Spiral\Views\ViewManager',

        //Modules
        'Spiral\Modules\PublisherInterface'                 => 'Spiral\Modules\Publisher',
        'Spiral\Modules\RegistratorInterface'               => 'Spiral\Modules\Registrator',

        //Default snapshot handler
        'Spiral\Debug\SnapshotInterface'                    => 'Spiral\Debug\QuickSnapshot',

        //Security component
        'Spiral\Security\PermissionsInterface'              => 'Spiral\Security\PermissionManager',
        'Spiral\Security\RulesInterface'                    => 'Spiral\Security\RuleManager',
        'Spiral\Security\GuardInterface'                    => 'Spiral\Security\Guard',

        //ODM and ORM
        'Spiral\ODM\ODMInterface'                           => 'Spiral\ODM\ODM',
        'Spiral\ORM\ORMInterface'                           => 'Spiral\ORM\ORM'
    ];
}