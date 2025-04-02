<?php

declare(strict_types=1);

use MonorepoBuilder\MostRecentTagResolver;
use MonorepoBuilder\ProcessParser;
use MonorepoBuilder\TagParserInterface;
use MonorepoBuilder\ValidateVersionReleaseWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Contract\Git\TagResolverInterface;
use Symplify\MonorepoBuilder\ValueObject\Option;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushNextDevReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;

/**
 * Monorepo Builder additional fields
 *
 * @see https://github.com/symplify/symplify/issues/2061
 */
\register_shutdown_function(static function () {
    $dest = \json_decode(\file_get_contents(__DIR__ . '/composer.json'), true);

    $result = [
        'name'              => 'spiral/framework',
        'type'              => 'library',
        'description'       => $dest['description'] ?? '',
        'homepage'          => 'https://spiral.dev',
        'license'           => 'MIT',
        'funding' => [
            [
                'type' => 'github',
                'url' => 'https://github.com/sponsors/spiral',
            ],
        ],
        'support'           => [
            'issues' => 'https://github.com/spiral/framework/issues',
            'source' => 'https://github.com/spiral/framework',
        ],
        'authors'           => [
            [
                'name'  => 'Anton Titov (wolfy-j)',
                'email' => 'wolfy-j@spiralscout.com',
            ],
            [
                'name'  => 'Pavel Butchnev (butschster)',
                'email' => 'pavel.buchnev@spiralscout.com',
            ],
            [
                'name'  => 'Aleksei Gagarin (roxblnfk)',
                'email' => 'alexey.gagarin@spiralscout.com',
            ],
            [
                'name'  => 'Maksim Smakouz (msmakouz)',
                'email' => 'maksim.smakouz@spiralscout.com',
            ],
        ],
        'require'           => $dest['require'] ?? [],
        'autoload'          => $dest['autoload'] ?? [],
        'require-dev'       => $dest['require-dev'] ?? [],
        'autoload-dev'      => $dest['autoload-dev'] ?? [],
        'replace'           => $dest['replace'] ?? [],
        'scripts'           => $dest['scripts'] ?? [],
        'extra'             => $dest['extra'] ?? [],
        'config'            => $dest['config'] ?? [],
        'minimum-stability' => $dest['minimum-stability'] ?? 'dev',
        'prefer-stable'     => $dest['prefer-stable'] ?? true,
    ];

    $json = \json_encode($result, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);

    \file_put_contents(__DIR__ . '/composer.json', $json . "\n");
});


return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PACKAGE_ALIAS_FORMAT, '<major>.<minor>.x-dev');
    $parameters->set(Option::PACKAGE_DIRECTORIES, ['src']);

    $parameters->set(Option::DATA_TO_APPEND, [
        'type'         => 'library',
        'autoload'     => [
            'files' => [
                'src/Framework/helpers.php',
            ],
            'psr-4' => [
                'Spiral\\' => 'src/Framework',
            ],
        ],
        'require'      => [
            'spiral/composer-publish-plugin' => '^1.0',
        ],
        'autoload-dev' => [
            'psr-4' => [
                'MonorepoBuilder\\'          => 'builder',
                'Spiral\\Tests\\Framework\\' => 'tests/Framework',
                'Spiral\\App\\'              => 'tests/app/src',
            ],
        ],
        'require-dev'  => [
            'phpunit/phpunit'           => '^10.5.41',
            'mockery/mockery'           => '^1.6.12',
            'spiral/code-style'         => '^2.2.2',
            'symplify/monorepo-builder' => '^10.3.3',
            'vimeo/psalm'               => '^6.0',
        ],
        'conflict' => [
            "spiral/roadrunner-bridge" => "<3.7",
            "spiral/sapi-bridge" => "<1.1"
        ],
    ]);

    $services = $containerConfigurator->services();

    $services->set(TagResolverInterface::class, MostRecentTagResolver::class);
    $services->set(TagParserInterface::class, ProcessParser::class)->autowire();

    # release workers - in order to execute
    $services->set(ValidateVersionReleaseWorker::class);
    $services->set(SetCurrentMutualDependenciesReleaseWorker::class);
    $services->set(AddTagToChangelogReleaseWorker::class);
    $services->set(TagVersionReleaseWorker::class);
    $services->set(PushTagReleaseWorker::class);
    $services->set(SetNextMutualDependenciesReleaseWorker::class);
    $services->set(UpdateBranchAliasReleaseWorker::class);
    $services->set(PushNextDevReleaseWorker::class);
};
