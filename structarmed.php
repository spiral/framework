<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

$layerPatterns = [
    'Auth' => '/^Spiral\\\\Auth\\\\.*$/',
    'Boot' => '/^Spiral\\\\Boot\\\\.*$/',
    'Broadcasting' => '/^Spiral\\\\Broadcasting\\\\.*$/',
    'Cache' => '/^Spiral\\\\Cache\\\\.*$/',
    'Config' => '/^Spiral\\\\Config\\\\.*$/',
    'Console' => '/^Spiral\\\\Console\\\\.*$/',
    'Cookies' => '/^Spiral\\\\Cookies\\\\.*$/',
    'Core' => '/^Spiral\\\\Core\\\\.*$/',
    'Csrf' => '/^Spiral\\\\Csrf\\\\.*$/',
    'Debug' => '/^Spiral\\\\Debug\\\\.*$/',
    'Distribution' => '/^Spiral\\\\Distribution\\\\.*$/',
    'DotEnv' => '/^Spiral\\\\DotEnv\\\\.*$/',
    'Encrypter' => '/^Spiral\\\\Encrypter\\\\.*$/',
    'Events' => '/^Spiral\\\\Events\\\\.*$/',
    'Exceptions' => '/^Spiral\\\\Exceptions\\\\.*$/',
    'Files' => '/^Spiral\\\\Files\\\\.*$/',
    'Filters' => '/^Spiral\\\\Filters\\\\.*$/',
    'Framework' => '/^Spiral\\\\(?:Attribute|Bootloader|Command|Domain|Filter|Framework|Module)(?:\\\\.*)?$/',
    'Http' => '/^Spiral\\\\Http\\\\.*$/',
    'Interceptors' => '/^Spiral\\\\Interceptors\\\\.*$/',
    'Logger' => '/^Spiral\\\\Logger\\\\.*$/',
    'Mailer' => '/^Spiral\\\\Mailer\\\\.*$/',
    'Models' => '/^Spiral\\\\Models\\\\.*$/',
    'Monolog' => '/^Spiral\\\\Monolog\\\\.*$/',
    'Pagination' => '/^Spiral\\\\Pagination\\\\.*$/',
    'Prototype' => '/^Spiral\\\\Prototype\\\\.*$/',
    'Queue' => '/^Spiral\\\\Queue\\\\.*$/',
    'Reactor' => '/^Spiral\\\\Reactor\\\\.*$/',
    'Router' => '/^Spiral\\\\Router\\\\.*$/',
    'Scaffolder' => '/^Spiral\\\\Scaffolder\\\\.*$/',
    'Security' => '/^Spiral\\\\Security\\\\.*$/',
    'SendIt' => '/^Spiral\\\\SendIt\\\\.*$/',
    'Serializer' => '/^Spiral\\\\Serializer\\\\.*$/',
    'Session' => '/^Spiral\\\\Session\\\\.*$/',
    'Snapshots' => '/^Spiral\\\\Snapshots\\\\.*$/',
    'Stempler' => '/^Spiral\\\\Stempler\\\\.*$/',
    'Storage' => '/^Spiral\\\\Storage\\\\.*$/',
    'Streams' => '/^Spiral\\\\Streams\\\\.*$/',
    'Telemetry' => '/^Spiral\\\\Telemetry\\\\.*$/',
    'Tokenizer' => '/^Spiral\\\\Tokenizer\\\\.*$/',
    'Translator' => '/^Spiral\\\\Translator\\\\.*$/',
    'Validation' => '/^Spiral\\\\Validation\\\\.*$/',
    'Views' => '/^Spiral\\\\Views\\\\.*$/',
];

$frameworkDependencies = array_values(array_diff(array_keys($layerPatterns), ['Framework']));

$ruleset = [
    'Auth' => ['Cache', 'Cookies', 'Core', 'Session'],
    'Boot' => ['Config', 'Core', 'Debug', 'Events', 'Exceptions', 'Files', 'Framework', 'Logger'],
    'Broadcasting' => ['Boot', 'Config', 'Core', 'Framework'],
    'Cache' => ['Boot', 'Config', 'Core', 'Files'],
    'Config' => ['Core'],
    'Console' => ['Boot', 'Config', 'Core', 'Events', 'Exceptions', 'Files', 'Framework', 'Interceptors', 'Logger', 'Tokenizer'],
    'Cookies' => ['Core', 'Encrypter', 'Framework'],
    'Core' => ['Interceptors', 'Security'],
    'Csrf' => ['Cookies', 'Core'],
    'Debug' => ['Boot', 'Core', 'Logger'],
    'Distribution' => ['Boot', 'Config', 'Core'],
    'DotEnv' => ['Boot', 'Core'],
    'Encrypter' => ['Core'],
    'Events' => ['Boot', 'Config', 'Core', 'Framework', 'Interceptors', 'Tokenizer'],
    'Exceptions' => ['Boot', 'Core', 'Debug', 'Filters', 'Http', 'Snapshots'],
    'Files' => [],
    'Filters' => ['Auth', 'Core', 'Interceptors', 'Models', 'Validation'],
    'Framework' => $frameworkDependencies,
    'Http' => ['Boot', 'Core', 'Exceptions', 'Files', 'Framework', 'Logger', 'Pagination', 'Router', 'Streams', 'Telemetry'],
    'Interceptors' => ['Core'],
    'Logger' => ['Boot', 'Core'],
    'Mailer' => [],
    'Models' => [],
    'Monolog' => ['Boot', 'Config', 'Core', 'Logger'],
    'Pagination' => [],
    'Prototype' => [
        'Auth',
        'Boot',
        'Broadcasting',
        'Cache',
        'Config',
        'Console',
        'Cookies',
        'Core',
        'Encrypter',
        'Events',
        'Exceptions',
        'Files',
        'Framework',
        'Http',
        'Interceptors',
        'Logger',
        'Pagination',
        'Queue',
        'Reactor',
        'Router',
        'Security',
        'Serializer',
        'Session',
        'Snapshots',
        'Storage',
        'Tokenizer',
        'Translator',
        'Validation',
        'Views',
    ],
    'Queue' => ['Boot', 'Config', 'Core', 'Exceptions', 'Interceptors', 'Serializer', 'Snapshots', 'Telemetry', 'Tokenizer'],
    'Reactor' => ['Files'],
    'Router' => ['Boot', 'Core', 'Framework', 'Http', 'Interceptors', 'Telemetry', 'Tokenizer'],
    'Scaffolder' => ['Boot', 'Config', 'Console', 'Core', 'Events', 'Files', 'Filters', 'Framework', 'Interceptors', 'Prototype', 'Queue', 'Reactor', 'Router', 'Validation'],
    'Security' => ['Console', 'Core', 'Events', 'Interceptors'],
    'SendIt' => ['Boot', 'Config', 'Core', 'Logger', 'Mailer', 'Queue', 'Stempler', 'Views'],
    'Serializer' => ['Boot', 'Config', 'Core'],
    'Session' => ['Cache', 'Cookies', 'Core', 'Files', 'Http'],
    'Snapshots' => ['Exceptions', 'Files', 'Storage'],
    'Stempler' => ['Boot', 'Config', 'Core', 'Files', 'Router', 'Translator', 'Views'],
    'Storage' => ['Boot', 'Config', 'Core', 'Distribution'],
    'Streams' => [],
    'Telemetry' => ['Boot', 'Config', 'Core', 'Logger'],
    'Tokenizer' => ['Boot', 'Config', 'Core', 'Framework', 'Logger'],
    'Translator' => ['Boot', 'Core', 'Logger', 'Tokenizer', 'Views'],
    'Validation' => ['Boot', 'Config', 'Core'],
    'Views' => ['Boot', 'Config', 'Core', 'Files'],
];

$architecture = Architecture::define()
    ->skipPaths([
        // fixtures
        'src/Tokenizer/tests/Enums',
        'src/Tokenizer/tests/Interfaces',
        'src/Tokenizer/tests/Classes',
        'src/Scaffolder/tests/App/config',

        // multiple classes in one file on purpose
        'src/Tokenizer/tests/ReflectionFileTest.php',
        'src/Core/tests/Internal/Proxy/ProxyClassRendererTest.php',

        // uses as bootstrapping
        'src/Core/tests/bootstrap.php',
    ])
    ->ruleset($ruleset);

foreach ($layerPatterns as $name => $pattern) {
    $architecture->layerPattern($name, $pattern);
}

return $architecture->withPreset(Preset::PSR4());
