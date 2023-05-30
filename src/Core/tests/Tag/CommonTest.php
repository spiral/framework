<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Tag;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\ContainerTagInterface;

final class CommonTest extends TestCase
{
    public function testContainerTagInterface(): void
    {
        $c = new Container();

        $this->assertInstanceOf(ContainerTagInterface::class, $c);
        $this->assertSame($c, $c->get(ContainerTagInterface::class));
    }
}
