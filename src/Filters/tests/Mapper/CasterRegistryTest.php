<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Mapper;

use PHPUnit\Framework\TestCase;
use Spiral\Filters\Model\Mapper\DefaultCaster;
use Spiral\Filters\Model\Mapper\CasterInterface;
use Spiral\Filters\Model\Mapper\CasterRegistry;

final class CasterRegistryTest extends TestCase
{
    public function testRegisterAndGetSetters(): void
    {
        $registry = new CasterRegistry();
        $this->assertCount(0, $registry->getSetters());

        $setter = $this->createMock(CasterInterface::class);
        $registry->register($setter);
        $this->assertCount(1, $registry->getSetters());
        $this->assertSame([$setter], $registry->getSetters());
    }

    public function testGetDefault(): void
    {
        $registry = new CasterRegistry();
        $this->assertInstanceOf(DefaultCaster::class, $registry->getDefault());
    }
}
