<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Views\ViewsBootloader;
use Spiral\SendIt\Renderer\ViewRenderer;
use Spiral\SendIt\RendererInterface;
use Spiral\Stempler\Bootloader\StemplerBootloader;

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

    public function boot(ViewsBootloader $views): void
    {
        $views->addDirectory('sendit', __DIR__ . '/../../views');
    }
}
