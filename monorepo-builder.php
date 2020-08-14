<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushNextDevReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # release workers - in order to execute
    $services->set(SetCurrentMutualDependenciesReleaseWorker::class);
    $services->set(AddTagToChangelogReleaseWorker::class);
    $services->set(TagVersionReleaseWorker::class);
    $services->set(PushTagReleaseWorker::class);
    $services->set(SetNextMutualDependenciesReleaseWorker::class);
    $services->set(UpdateBranchAliasReleaseWorker::class);
    $services->set(PushNextDevReleaseWorker::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PACKAGE_ALIAS_FORMAT, '<major>.<minor>.x-dev');

    $parameters->set(Option::PACKAGE_DIRECTORIES, [
        __DIR__ . '/src',
    ]);

    $parameters->set(Option::DATA_TO_APPEND, [
        'data_to_append' => [
            'autoload'     => [
                'files' => [
                    'src/Framework/helpers.php',
                ],
                'psr-4' => [
                    'Spiral\\' => 'src/Framework',
                ],
            ],
            'require'      => [
                'spiral/boot'                    => '^1.1',
                'spiral/tokenizer'               => '^2.0',
                'spiral/composer-publish-plugin' => '^1.0',
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'Spiral\\Tests\\Framework\\' => 'tests/Framework',
                    'Spiral\\Tests\\'            => 'tests/app/src',
                ],
            ],
            'require-dev'  => [
                'roave/security-advisories' => 'dev-master',
                'phpunit/phpunit'           => '^8.5|^9.0',
                'mockery/mockery'           => '^1.1',
                'symplify/monorepo-builder' => '^8.0',
                'spiral/code-style'         => '^1.0',
                'spiral/logger'             => '^1.0',
                'spiral/debug'              => '^1.1',
                'spiral/encrypter'          => '^1.0',
                'spiral/snapshots'          => '^1.0',
                'spiral/translator'         => '^1.2',
                'spiral/console'            => '^1.0',
                'spiral/http'               => '^1.0',
                'spiral/cookies'            => '^1.2',
                'spiral/csrf'               => '^1.1',
                'spiral/router'             => '^1.0',
                'spiral/session'            => '^1.2.2',
                'spiral/roadrunner'         => '^1.2',
                'spiral/models'             => '^2.0',
                'spiral/validation'         => '^1.4.4',
                'spiral/filters'            => '^1.0',
                'spiral/security'           => '^2.0',
                'spiral/database'           => '^2.4',
                'spiral/migrations'         => '^2.0',
                'spiral/views'              => '^1.0',
                'spiral/php-grpc'           => '^1.0',
                'spiral/jobs'               => '^2.0',
                'cycle/orm'                 => '^1.0',
                'cycle/annotated'           => '^2.0',
                'cycle/migrations'          => '^1.0',
                'cycle/proxy-factory'       => '^1.0',
                'spiral/auth'               => '^1.0',
                'spiral/auth-http'          => '^1.1',
                'spiral/broadcast'          => '^2.0',
                'spiral/broadcast-ws'       => '^1.0',
            ],
        ],
    ]);

    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES, [
        __DIR__ . '/src/Core' => 'git@github.com:spiral/core.git',
    ]);
};
