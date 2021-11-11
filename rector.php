<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src/*/src',
    ]);

    $parameters->set(Option::SKIP, [
        CountOnNullRector::class,
    ]);

    $containerConfigurator->import(LevelSetList::UP_TO_PHP_72);
};
