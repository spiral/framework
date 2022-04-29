<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Views;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\I18nBootloader;
use Spiral\Translator\Views\LocaleDependency;
use Spiral\Translator\Views\LocaleProcessor;
use Spiral\Views\Bootloader\ViewsBootloader;

/**
 * Generates unique cache path based on active translator locale.
 */
final class TranslatedCacheBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        I18nBootloader::class,
    ];

    protected const SINGLETONS = [
        // Each engine expect to mount this process by itself
        LocaleProcessor::class => LocaleProcessor::class,
    ];

    /**
     * @param ViewsBootloader $views
     */
    public function init(ViewsBootloader $views): void
    {
        $views->addCacheDependency(LocaleDependency::class);
    }
}
