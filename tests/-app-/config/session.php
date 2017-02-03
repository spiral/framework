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
    'handler'  => env('SESSION_HANDLER', null),
    /*
     * Session handler.s
     */
    'handlers' => [
        'null'  => [
            'class' => Handlers\NullHandler::class
        ],
        /*
         * Think twice before using this session store in production.
         */
        'files' => [
            'class'   => Handlers\FileHandler::class,
            'options' => [
                'directory' => directory('runtime') . '/sessions'
            ]
        ],
        /*{{handlers}}*/
    ]
];
