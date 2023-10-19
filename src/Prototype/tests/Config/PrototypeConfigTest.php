<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Prototype\Config\PrototypeConfig;

final class PrototypeConfigTest extends TestCase
{
    public function testGetBindings(): void
    {
        $config = new PrototypeConfig();
        $this->assertSame([], $config->getBindings());

        $config = new PrototypeConfig(['bindings' => ['foo' => 'test', 'bar' => ['with' => [], 'resolve' => 'test2']]]);
        $this->assertSame(['foo' => 'test', 'bar' => ['with' => [], 'resolve' => 'test2']], $config->getBindings());
    }
}
