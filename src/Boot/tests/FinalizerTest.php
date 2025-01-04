<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\Finalizer;

class FinalizerTest extends TestCase
{
    public function testFinalize(): void
    {
        $f = new Finalizer();

        $value = 1;
        $f->addFinalizer(function () use (&$value): void {
            $value = 2;
        });

        self::assertSame(1, $value);
        $f->finalize();
        self::assertSame(2, $value);
    }
}
