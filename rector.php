<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src/*/src',
    ])
    ->withParallel()
    ->withSkip([
        IfIssetToCoalescingRector::class,
        RemoveUnusedPrivatePropertyRector::class => [
            __DIR__ . '/src/Scaffolder/src/Command/BootloaderCommand.php',
            __DIR__ . '/src/Scaffolder/src/Command/CommandCommand.php',
            __DIR__ . '/src/Scaffolder/src/Command/ConfigCommand.php',
            __DIR__ . '/src/Scaffolder/src/Command/ControllerCommand.php',
            __DIR__ . '/src/Scaffolder/src/Command/FilterCommand.php',
            __DIR__ . '/src/Scaffolder/src/Command/JobHandlerCommand.php',
            __DIR__ . '/src/Scaffolder/src/Command/MiddlewareCommand.php',
        ],
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
            __DIR__ . '/src/Stempler/src/Transform/Merge/ExtendsParent.php',
        ],
        RemoveExtraParametersRector::class => [
            __DIR__ . '/src/Boot/src/BootloadManager/AbstractBootloadManager.php',
        ],
        RemoveUnusedPrivateMethodParameterRector::class => [
            __DIR__ . '/src/Core/src/Internal/Factory.php',
        ],

        // to be enabled later after upgrade to 1.2.4 merged
        // to easier to review
        RemoveUnusedPublicMethodParameterRector::class,
        RemoveEmptyClassMethodRector::class,
        RemoveUnusedPromotedPropertyRector::class,
    ])
    ->withPhpSets(php72: true)
    ->withPreparedSets(deadCode: true);
