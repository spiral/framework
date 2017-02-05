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
     * Cookie name for sessions. Used by SessionStarter middleware. Other cookies options will
     * be gathered from HttpConfig. You can combine SessionStarter with CookieManager in order
     * to protect your cookies.
     */
    'cookie'   => env('SESSION_COOKIE', 'SID'),

    /*
     * Default handler is set to file. You can switch this values based on your environments.
     * SessionStore will be initiated on demand to prevent performance issues. Since spiral provides
     * set of widgets to with html forms over ajax sessions are mainly used to store authorization
     * data and not used to flush errors at page.
     *
     * You can set this value to "native" to disable custom session handler and use default php
     * mechanism.
     */
    'handler'  => env('SESSION_HANDLER', 'files'),

    /*
     * Session handler. You are able to use bind() function in handler options.
     */
    'handlers' => [
        //Debug session handler without ability to save anything
        'null'  => [
            'class' => Handlers\NullHandler::class
        ],
        //File based session
        'files' => [
            'class'   => Handlers\FileHandler::class,
            'options' => [
                'directory' => directory('runtime') . 'sessions'
            ]
        ],
        //Session with data storage located in external simple cache adapter
        'cache' => [
            'class'   => Handlers\CacheHandler::class,
            'options' => [
                'cache' => bind(\Psr\SimpleCache\CacheInterface::class)
            ]
        ]
        /*{{handlers}}*/
    ]
];