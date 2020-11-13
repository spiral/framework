<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader;

use Spiral\Tests\Attributes\Fixture\ClassAnnotation;

abstract class ReaderImplementationTest extends ReaderTest
{
    /**
     * @return string
     */
    abstract protected function getClassMetadata(): string;

    /**
     * @return string
     */
    abstract protected function getImplementationClass(): string;

    /**
     * @return void
     */
    public function testClassMetadataReadable(): void
    {
        $reflection = new \ReflectionClass($this->getImplementationClass());

        $this->assertCount(2, $this->reader->getClassMetadata($reflection));

        foreach ($this->reader->getClassMetadata($reflection) as $attribute) {
            $this->assertInstanceOf($this->getClassMetadata(), $attribute);
        }
    }

    /**
     * @return void
     */
    public function testClassMetadataInstanceOf(): void
    {
        $reflection = new \ReflectionClass($this->getImplementationClass());

        $this->assertCount(2, $this->reader->getClassMetadata($reflection));

        foreach ($this->reader->getClassMetadata($reflection, ClassAnnotation::class) as $attribute) {
            $this->assertInstanceOf($this->getClassMetadata(), $attribute);
        }
    }

    /**
     * @return void
     */
    public function testClassMetadataNegativeInstanceOf(): void
    {
        $reflection = new \ReflectionClass($this->getImplementationClass());

        $this->assertCount(0, $this->reader->getClassMetadata($reflection, self::class));
    }

    public function testClassMetadataValues(): void
    {
        $reflection = new \ReflectionClass($this->getImplementationClass());
        $class = $this->getClassMetadata();

        /** @var ClassAnnotation[] $expected */
        $expected = [new $class(), new $class()];
        end($expected)->field = 'value';

        $this->assertEquals($expected, [...$this->reader->getClassMetadata($reflection)]);
    }
}
