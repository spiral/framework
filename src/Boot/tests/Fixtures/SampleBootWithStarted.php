<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

class SampleBootWithStarted extends Bootloader
{
    public const BOOT = true;

    public const BINDINGS   = ['abc' => self::class];
    public const SINGLETONS = ['single' => self::class];

    public function boot(BinderInterface $binder): void
    {
        $binder->bind('def', new SampleBoot());
    }

    public function start(BinderInterface $binder): void
    {
        $binder->bind('efg', new SampleClass());
        $binder->bind('ghi', 'foo');
    }
}
