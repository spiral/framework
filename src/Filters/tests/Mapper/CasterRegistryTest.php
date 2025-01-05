<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Mapper;

use PHPUnit\Framework\TestCase;
use Spiral\Filters\Model\Mapper\DefaultCaster;
use Spiral\Filters\Model\Mapper\CasterInterface;
use Spiral\Filters\Model\Mapper\CasterRegistry;

final class CasterRegistryTest extends TestCase
{
    public function testRegisterAndGetCasters(): void
    {
        $registry = new CasterRegistry();
        self::assertCount(0, $registry->getCasters());

        $setter = $this->createMock(CasterInterface::class);
        $registry->register($setter);
        self::assertCount(1, $registry->getCasters());
        self::assertSame([$setter], $registry->getCasters());
    }

    public function testGetDefault(): void
    {
        $registry = new CasterRegistry();
        self::assertInstanceOf(DefaultCaster::class, $registry->getDefault());
    }
}
