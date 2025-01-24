<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Destructor;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class MemoryLeaksTest extends TestCase
{
    public function testInternalServicesDontBlockContainer(): void
    {
        $container = new Container();
        $refLink = \WeakReference::create($container);
        unset($container);

        $this->assertNull($refLink->get());
    }

    public function testInternalServicesDontLeaks(): void
    {
        $container = new Container();
        $refLink = \WeakReference::create($container);
        $map = $this->collectInternal($container);

        unset($container);

        $this->assertNull($refLink->get());
        $this->assertEmpty($map);
    }

    private function collectInternal(object $source): \WeakMap
    {
        $map = new \WeakMap();

        $fn = function (\WeakMap $map) {
            foreach ($this as $key => $value) {
                if (\is_object($value)) {
                    $map->offsetSet($value, $key);
                }
            }
        };
        $fn->call($source, $map);

        return $map;
    }
}
