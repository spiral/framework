<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $config): void {
    $config->paths([
        __DIR__ . '/src/*/src',
    ]);

    $config->parallel();
    $config->skip([
        CountOnNullRector::class,
        RemoveUnusedPrivateMethodRector::class => [
            __DIR__ . '/src/Boot/src/Bootloader/ConfigurationBootloader.php',
            __DIR__ . '/src/Broadcasting/src/Bootloader/BroadcastingBootloader.php',
            __DIR__ . '/src/Cache/src/Bootloader/CacheBootloader.php',
            __DIR__ . '/src/Serializer/src/Bootloader/SerializerBootloader.php',
            __DIR__ . '/src/Validation/src/Bootloader/ValidationBootloader.php',
        ],
        RemoveUselessVarTagRector::class => [
            __DIR__ . '/src/Console/src/Traits/HelpersTrait.php',
        ],
        RemoveAlwaysTrueIfConditionRector::class => [
            __DIR__ . '/src/Boot/src/BootloadManager/Initializer.php',
            __DIR__ . '/src/Stempler/src/Traverser.php',
            __DIR__ . '/src/Prototype/src/NodeVisitors/LocateProperties.php',
            __DIR__ . '/src/Prototype/src/NodeVisitors/RemoveTrait.php',
            __DIR__ . '/src/Logger/src/ListenerRegistry.php',
        ]
    ]);

    $config->import(LevelSetList::UP_TO_PHP_72);
    $config->import(SetList::DEAD_CODE);
};
