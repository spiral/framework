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

class SampleBoot extends Bootloader
{
    public const BOOT = true;

    public const BINDINGS   = ['abc' => self::class];
    public const SINGLETONS = ['single' => self::class];

    public function boot(BinderInterface $binder): void
    {
        $binder->bind('cde', new SampleClass());
    }
}
