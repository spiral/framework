<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

class SampleBootWithMethodBoot extends Bootloader
{
    public const BOOT = true;
    public const BINDINGS   = ['abc' => self::class];
    public const SINGLETONS = ['single' => self::class];

    public function init(BinderInterface $binder): void
    {
        $binder->bind('def', new SampleBoot());
    }

    public function boot(BinderInterface $binder): void
    {
        $binder->bind('efg', new SampleClass());
        $binder->bind('ghi', 'foo');
    }
}
