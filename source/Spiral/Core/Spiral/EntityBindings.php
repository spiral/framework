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
 * Some shared bindings for spiral services.
 */
class EntityBindings extends Initializer
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