<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src/*/src',
    ]);

    $rectorConfig->importShortClasses(false);
    $rectorConfig->importNames();
    $rectorConfig->parallel();
    $rectorConfig->skip([
        CountOnNullRector::class,

        RemoveUnusedPrivateMethodRector::class => [
            __DIR__ . '/src/Boot/src/Bootloader/ConfigurationBootloader.php',
            __DIR__ . '/src/Broadcasting/src/Bootloader/BroadcastingBootloader.php',
        ],

        // deprecated classes
        __DIR__ . '/src/Http/src/Exception/EmitterException.php',
        __DIR__ . '/src/Dumper/src/Exception/DumperException.php',
    ]);
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_74, SetList::DEAD_CODE]);
};
