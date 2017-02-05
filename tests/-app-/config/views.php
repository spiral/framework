<?php
/**
 * ViewManager component configuration file. Attention, configs might include runtime code which
 * depended on environment values only.
 *
 * @see ViewsConfig
 */
use Spiral\Views\Engines;
use Spiral\Views\Processors;

return [
    'cache' => [
        /*
         * Indicates that view engines must enable caching for their templates, you can reset existed
         * view cache by executing command "view:compile"
         */
        'enabled'   => env('VIEW_CACHE'),
        /*
         * Location where view cache has to be stored into. By default you can use
         * app/runtime/cache/views directory.
         */
        'directory' => directory("cache") . 'views/'
    ],

    'namespaces'  => [
        /*
         * This is default application namespace which can be used without any prefix.
         */
        'default' => [
            directory("application") . 'views/',
            /*{{namespaces.default}}*/
        ],
        /*
         * This namespace contain few framework views like http error pages and exception view
         * used in snapshots. In addition, same namespace used by Toolkit module to share it's
         * views and widgets.
         */
        'spiral'  => [
            directory("framework") . 'views/',
            /*{{namespaces.spiral}}*/
        ],
        /*{{namespaces}}*/
    ],

    /*
     * Environment variable define what cache version to be used for different engines, it primary
     * goal is to provide ability to evaluate some functionality at compilation (not runtime) phase.
     */
    'environment' => [
        'language' => ['translator', 'getLocale'],
        'basePath' => ['http', 'basePath'],
        /*{{environment}}*/
    ],

    /*
     * You can connect as many engines as you want, simply declare engine name, class and extension
     * to be handled. Every engine class resolve using container, you are able to define your own
     * dependencies in your implementation.
     */
    'engines'     => [
        /*
         * You can always extend TwigEngine class and define your own configuration rules in it.
         */
        'twig'   => [
            'class'      => Engines\TwigEngine::class,
            'extension'  => 'twig',
            'options'    => [
                'auto_reload' => true
            ],

            /*
            * Modifiers applied to imported or extended view source before it's getting parsed by
            * HtmlTemplater, every modifier has to implement ModifierInterface and as result view
            * name, namespace and filename are available for it. Modifiers is the best to connect
            * custom syntax processors (for example Laravel's Blade).
            */
            'modifiers'  => [
                //Automatically replaces [[string]] with their translations
                Processors\TranslateProcessor::class,

                //Mounts view environment variables using @{name} pattern.
                Processors\EnvironmentProcessor::class,

                /*{{twig.modifiers}}*/
            ],

            /*
            * Here you define list of extensions to be mounted into twig engine, every extension
            * class will be resolved using container so you can use constructor dependencies.
            */
            'extensions' => [
                //Provides access to dump() and spiral() functions inside twig templates
                Engines\Twig\Extensions\SpiralExtension::class
                /*{{twig.extension}}*/
            ]
        ],
        /*
         * Stempler does not provide any custom command syntax (however you can connect one using
         * modifiers section), instead it compose templates together using html tags based on
         * defined syntax (in our case "Dark").
         */
        'dark'   => [
            'class'      => Engines\StemplerEngine::class,

            /*
             * Do not change this extension, it used across spiral toolkit, profiler and
             * administration modules.
             */
            'extension'  => 'dark.php',

            /*
             * Modifiers applied to imported or extended view source before it's getting parsed by
             * HtmlTemplater, every modifier has to implement ModifierInterface and as result view
             * name, namespace and filename are available for it. Modifiers one of the options to
             * connect custom syntax processors (for example Laravel's Blade or Nette Latte).
             */
            'modifiers'  => [
                //Automatically replaces [[string]] with their translations
                Processors\TranslateProcessor::class,

                //Mounts view environment variables using @{name} pattern.
                Processors\EnvironmentProcessor::class,

                //This modifier automatically replace some php constructors with evaluated php code,
                //such modifier used in spiral toolkit to simplify widget includes (see documentation
                //and examples).
                Processors\ExpressionsProcessors::class,

                /*{{dark.modifiers}}*/
            ],

            /*
             * Processors applied to compiled view source after templating work is done and view is
             * fully composited.
             */
            'processors' => [
                //Evaluates php block with #compile comment at moment of template compilation
                Processors\EvaluateProcessor::class,

                //Drops empty lines and normalize attributes
                Processors\PrettifyProcessor::class,

                /*{{dark.processors}}*/
            ]
        ],
        /*
         * Native engine simply executes php file without any additional features. You can access
         * NativeView object using variable $this from your view code, to get instance of view
         * container use $this->container.
         */
        'native' => [
            'class'     => Engines\NativeEngine::class,
            'extension' => 'php'
        ],
        /*{{engines}}*/
    ]
];