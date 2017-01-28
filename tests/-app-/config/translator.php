<?php
/**
 * Translator component configuration. Attention, configs might include runtime code which depended
 * on environment values only.
 *
 * @see TranslatorConfig
 */
use Symfony\Component\Translation;

return [
    /*
     * Default application locale.
     */
    'locale'           => 'en',

    /*
     * Locale to be used if no translation found in currently active locale. Additionally this
     * locale, going to be used to automatically register new messages.
     */
    'fallbackLocale'   => 'en',

    /*
     * This configuration section contain list of domains associated with their bundles (using
     * patterns).
     *
     * All classes which use trait TranslatorTrait automatically get bundle name equals to full class
     * name (including names). Identical pattern with views, however view bundle includes prefix
     * defined in translator modifier ("view-").
     */
    'domains'          => [
        'validation' => [
            'spiral-validation-*',
            /*{{domains.validation}}*/
        ],
        'spiral'     => [
            'spiral-*',
            'view-spiral-*',
            /*{{domains.spiral}}*/
        ],
        'profiler'   => [
            'view-profiler-*',
            /*{{domains.profiler}}*/
        ],
        'views'      => [
            'view-*',
            /*{{domains.views}}*/
        ],
        'requests'   => [
            'requests-*'
        ],
        'external'   => ['external'],
        'messages'   => ['*'],
        /*{{domains}}*/
    ],

    /*
     * Directory where localization files has to be stored.
     */
    'localesDirectory' => directory('locales'),

    /*
     * Loaders to be used to read locale data. Locale data must locate in a directory under
     * localesDirectory, every file inside such directory must represent one locale domain, extension
     * has to correlate with one of this loaders (symfony translation loaders). Domain name will be
     * fetched from filename (string before first ".")!
     */
    'loaders'          => [
        'php' => Translation\Loader\PhpFileLoader::class,
        'csv' => Translation\Loader\CsvFileLoader::class,
        'po'  => Translation\Loader\PoFileLoader::class,
        /*{{loaders}}*/
    ],

    /*
     * You can define dumpers to be used in console command "i18n:dump", every id/name must be
     * associated with appropriate symfony dumper class and file extension.
     *
     * Attention, dumping will be performed into directory, not file. Generated
     */
    'dumpers'          => [
        'xliff' => Translation\Dumper\XliffFileDumper::class,
        'php'   => Translation\Dumper\PhpFileDumper::class,
        'po'    => Translation\Dumper\PoFileDumper::class,
        /*{{exporters}}*/
    ],

    /*
     * When true Translator will be reloading application messages from hard drive on every request.
     * This option might simplify development but slow down your application a bit.
     */
    'autoReload'       => !env('TRANSLATOR_CACHE'),

    /*
     * Automatically register all missed string into fallback locale, attention, this options can
     * slow down your application, however it can speed up development drastically when combined
     * with locale dumping.
     */
    'autoRegister'     => true
];
