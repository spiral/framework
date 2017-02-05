<?php
/**
 * Http configuration used across framework, not only in HttpDispatcher. Attention, configs might
 * include runtime code which depended on environment values only.
 *
 * @see HttpConfig
 */
use Spiral\Http;
use Spiral\Http\Cookies;
use Spiral\Http\Middlewares;

return [
    /*
     * Base application path is required to perform valid routing.
     */
    'basePath'     => '/',

    /*
     * When set to false http dispatcher will stop reacting on application exception and will
     * return only 500 error page.
     */
    'exposeErrors' => env('DEBUG', false),

    /*
     * Configuration options for CookieManager middleware.
     */
    'cookies'      => [
        //You force specific domain or use pattern like ".{host}" to share cookies with sub domains
        'domain' => null,

        //Cookie protection method
        'method' => Http\Configs\HttpConfig::COOKIE_ENCRYPT,

        'excluded' => [
            /*{{cookies.excluded}}*/
        ]
    ],

    /*
     * Configuration options for CSRF middleware.
     */
    'csrf'         => [
        'cookie'   => 'csrf-token',
        'length'   => 64,
        'lifetime' => null
    ],

    /*
     * Set of headers to be added to every response by default.
     */
    'headers'      => [
        'Content-Type' => 'text/html; charset=UTF-8',
        'Generator'    => 'Spiral',
        /*{{headers}}*/
    ],

    /*
     * You can set your own endpoint class here to change spiral http flow.
     */
    'endpoint'     => null,

    /*
     * When no endpoints set HttpDispatcher will use constructed router instance.
     */
    'router'       => [
        //You can use your own router or entirely replace http endpoint using option above
        'class'   => Http\Routing\Router::class,
        'options' => []
    ],

    /*
     * This is set of "global" middlewares, they will be applied to every request before endpoint
     * executed.
     */
    'middlewares'  => [
        Middlewares\CsrfMiddleware::class,
        Middlewares\ExceptionWrapper::class,
        Cookies\CookieManager::class,

        //Session\Http\SessionStarter::class,

        /*{{middlewares}}*/
    ],

    /*
     * ExceptionIsolator middleware can automatically handle ClientExceptions and populate response
     * with corresponding error page. This list must contain array of error codes associated with
     * view files.
     */
    'httpErrors'   => [
        400 => 'spiral:http/400',
        403 => 'spiral:http/403',
        404 => 'spiral:http/404',
        500 => 'spiral:http/500',
        /*{{errors}}*/
    ]
];
