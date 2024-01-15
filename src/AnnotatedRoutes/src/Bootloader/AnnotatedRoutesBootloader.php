<?php

declare(strict_types=1);

namespace Spiral\Router\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Core\Attribute\Singleton;
use Spiral\Router\RouteLocatorListener;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

/**
 * Configures application routes using annotations and pre-defined configuration groups.
 */
#[Singleton]
final class AnnotatedRoutesBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        RouterBootloader::class,
        AttributesBootloader::class,
        TokenizerListenerBootloader::class,
    ];

    public function init(
        TokenizerListenerRegistryInterface $listenerRegistry,
        RouteLocatorListener $routeRegistrar
    ): void {
        $listenerRegistry->addListener($routeRegistrar);
    }
}
