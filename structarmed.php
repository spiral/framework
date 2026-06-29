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
    'Boot' => ['+Config', 'Debug', 'Events', 'Exceptions', 'Files', 'Framework', 'Logger'],
    'Broadcasting' => ['Boot', '+Config', 'Framework'],
    'Cache' => ['Boot', '+Config', 'Files'],
    'Config' => ['Core'],
    'Console' => ['+Events', 'Exceptions', 'Files', 'Logger'],
    'Cookies' => ['+Encrypter', 'Framework'],
    'Core' => ['Interceptors', 'Security'],
    'Csrf' => ['Cookies', 'Core'],
    'Debug' => ['+Logger'],
    'Distribution' => ['Boot', '+Config'],
    'DotEnv' => ['Boot', 'Core'],
    'Encrypter' => ['Core'],
    'Events' => ['Boot', '+Config', 'Framework', 'Interceptors', 'Tokenizer'],
    'Exceptions' => ['Boot', 'Core', 'Debug', 'Filters', 'Http', 'Snapshots'],
    'Files' => [],
    'Filters' => ['Auth', '+Interceptors', 'Models', 'Validation'],
    'Framework' => $frameworkDependencies,
    'Http' => ['Exceptions', 'Files', 'Framework', '+Logger', 'Pagination', 'Router', 'Streams', 'Telemetry'],
    'Interceptors' => ['Core'],
    'Logger' => ['Boot', 'Core'],
    'Mailer' => [],
    'Models' => [],
    'Monolog' => ['Config', '+Logger'],
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
    'Queue' => ['Exceptions', 'Interceptors', '+Serializer', 'Snapshots', 'Telemetry', 'Tokenizer'],
    'Reactor' => ['Files'],
    'Router' => ['Boot', 'Framework', 'Http', '+Interceptors', 'Telemetry', 'Tokenizer'],
    'Scaffolder' => ['Console', 'Events', 'Files', 'Filters', 'Framework', 'Interceptors', 'Prototype', 'Queue', 'Reactor', 'Router', '+Validation'],
    'Security' => ['Console', 'Events', '+Interceptors'],
    'SendIt' => ['Config', '+Logger', 'Mailer', 'Queue', 'Stempler', 'Views'],
    'Serializer' => ['Boot', '+Config'],
    'Session' => ['Cache', 'Cookies', 'Core', 'Files', 'Http'],
    'Snapshots' => ['Exceptions', 'Files', 'Storage'],
    'Stempler' => ['Router', 'Translator', '+Views'],
    'Storage' => ['+Distribution'],
    'Streams' => [],
    'Telemetry' => ['Config', '+Logger'],
    'Tokenizer' => ['Config', 'Framework', '+Logger'],
    'Translator' => ['+Logger', 'Tokenizer', 'Views'],
    'Validation' => ['Boot', '+Config'],
    'Views' => ['Boot', '+Config', 'Files'],
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
