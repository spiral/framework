<?php

declare(strict_types=1);

namespace Spiral\SendIt\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\SendIt\Renderer\ViewRenderer;
use Spiral\SendIt\RendererInterface;
use Spiral\Stempler\Bootloader\StemplerBootloader;
use Spiral\Views\Bootloader\ViewsBootloader;

/**
 * Enables stempler email building DSL.
 */
final class BuilderBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        StemplerBootloader::class,
    ];

    protected const SINGLETONS = [
        RendererInterface::class => ViewRenderer::class,
    ];

    public function init(ViewsBootloader $views): void
    {
        $views->addDirectory('sendit', __DIR__ . '/../../views');
    }
}
