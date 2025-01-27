<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Destructor;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class FinalizerTest extends TestCase
{
    public function testInternalServicesDontBlockContainer(): void
    {
        (static function (): void {
            $container = new Container();
            $finalizer = new class {
                public ?\Closure $closure = null;

                public function __destruct()
                {
                    if ($this->closure !== null) {
                        ($this->closure)();
                    }
                }
            };
            $finalizer->closure = static function () use ($container): void {
                $container->hasInstance('finalizer');
            };
            $container->bind('finalizer', $finalizer);
        })();
        \gc_collect_cycles();
        $this->assertTrue(true);
    }
}
