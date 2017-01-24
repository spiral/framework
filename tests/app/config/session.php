<?php
/**
 * SessionStore configuration. Attention, configs might include runtime code which depended on
 * environment values only.
 *
 * @see SessionConfig
 */
use Spiral\Session\Handlers;

return [
    /*
     * Default session lifetime is 1 day.
     */
    'lifetime' => 86400,
    
    /*
     * Cookie name for sessions. Used by SessionStarter middleware.
     */
    'cookie'   => env('SESSION_COOKIE', 'PHPSESSID'),
    
    /*
     * Default handler is set to file. You can switch this values based on your environments.
     * SessionStore will be initiated on demand to prevent performance issues. Since spiral provides
     * set of widgets to with html forms over ajax sessions are mainly used to store authorization
     * data and not used to flush errors at page.
     *
     * You can set this value to "native" to disable custom session handler and use default php
     * mechanism.
     */
    'handler'  => env('SESSION_HANDLER', false),
    
    'handlers' => [
        'null'  => [
            'class' => Handlers\NullHandler::class
        ],
        /*
         * Think twice before using this session store in production.
         */
        'file'  => [
            'class'   => Handlers\FileHandler::class,
            'options' => [
                'directory' => directory('runtime') . '/sessions'
            ]
        ],
        /*
         * This handler provides ability to use any cache store as storage.
         */
        'cache' => [
            'class'   => Handlers\CacheHandler::class,
            'options' => [
                'store'  => 'memcache',
                'prefix' => 'session'
            ]
        ],
        /*{{handlers}}*/
    ]
];
