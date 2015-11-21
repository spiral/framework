<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Spiral;

use Spiral\Core\Initializers\Initializer;

/**
 * Shared components and short bindings.
 */
class ShortComponents extends Initializer
{
    /**
     * No need to boot, all cached.
     */
    const BOOT = false;

    /**
     * @var array
     */
    protected $bindings = [
        //Core components (see SharedTrait)
        'memory'    => 'Spiral\Core\HippocampusInterface',
        'modules'   => 'Spiral\Modules\ModuleManager',
        'debugger'  => 'Spiral\Debug\Debugger',

        //Dispatchers
        'http'      => 'Spiral\Http\HttpDispatcher',
        'console'   => 'Spiral\Console\ConsoleDispatcher',

        //Shared components
        'files'     => 'Spiral\Files\FileManager',
        'tokenizer' => 'Spiral\Tokenizer\Tokenizer',
        'locator'   => 'Spiral\Tokenizer\ClassLocator',
        'i18n'      => 'Spiral\Translator\Translator',
        'views'     => 'Spiral\Views\ViewManager',
        'storage'   => 'Spiral\Storage\StorageManager',

        //Databases and models
        'dbal'      => 'Spiral\Database\DatabaseManager',
        'orm'       => 'Spiral\ORM\ORM',
        'odm'       => 'Spiral\ODM\ODM',
    ];
}