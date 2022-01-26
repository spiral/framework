<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src/*/src',
    ]);

    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::SKIP, [
        CountOnNullRector::class,

        // for PHP 8
        RemoveUnusedPromotedPropertyRector::class,

        RemoveUnusedPrivateMethodRector::class => [
            __DIR__ . '/src/Boot/src/Bootloader/ConfigurationBootloader.php',
        ],
    ]);

    $containerConfigurator->import(LevelSetList::UP_TO_PHP_72);
    $containerConfigurator->import(SetList::DEAD_CODE);
};
