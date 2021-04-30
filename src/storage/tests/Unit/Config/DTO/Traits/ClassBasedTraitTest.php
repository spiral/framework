<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit\Config\DTO\Traits;

use Spiral\Storage\Exception\StorageException;
use Spiral\Tests\Storage\Unit\UnitTestCase;
use Spiral\Storage\Config\DTO\Traits\ClassBasedTrait;

class ClassBasedTraitTest extends UnitTestCase
{
    /**
     * @var ClassBasedTrait
     */
    private $trait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trait = $this->getMockForTrait(ClassBasedTrait::class);
    }

    /**
     * @throws StorageException
     */
    public function testSetClass(): void
    {
        $this->assertInstanceOf(get_class($this->trait), $this->trait->setClass(static::class));
    }

    public function testSetClassFailed(): void
    {
        $wrongClass = static::class . 1;

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(
            \sprintf('Class `%s` not exists. %s', $wrongClass, '')
        );

        $this->trait->setClass($wrongClass);
    }

    /**
     * @throws StorageException
     */
    public function testGetClass(): void
    {
        $this->trait->setClass(static::class);

        $this->assertEquals(static::class, $this->trait->getClass());
    }
}
