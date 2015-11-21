<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Spiral;

use Spiral\Core\Bootloaders\Bootloader;

/**
 * Some shared bindings for spiral services.
 */
class ShortEntities extends Bootloader
{
    /**
     * No boot.
     */
    const BOOT = false;

    /**
     * @var array
     */
    protected $bindings = [
        'encrypter' => 'Spiral\Encrypter\Encrypter',
        'cache'     => 'Spiral\Cache\CacheStore',
        'db'        => 'Spiral\Database\Entities\Database',
        'mongo'     => 'Spiral\ODM\Entities\MongoDatabase',

        //Scope dependent
        'session'   => 'Spiral\Session\SessionStore',
        'input'     => 'Spiral\Http\Input\InputManager',
        'cookies'   => 'Spiral\Http\Cookies\CookieManager',
        'router'    => 'Spiral\Http\Routing\Router',
        'request'   => 'Psr\Http\Message\ServerRequestInterface',
        'response'  => 'Psr\Http\Message\ResponseInterface',
    ];
}