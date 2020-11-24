<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader;

use Spiral\Tests\Attributes\Concerns\InteractWithMetadata;
use Spiral\Tests\Attributes\Fixture\AnnotatedClass;
use Spiral\Tests\Attributes\Fixture\Annotation\ClassAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\ConstantAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\FunctionAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\FunctionParameterAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\MethodAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\MethodParameterAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\PropertyAnnotation;
use Spiral\Tests\Attributes\TestCase;

abstract class ReaderTestCase extends TestCase
{
    use InteractWithMetadata;

    /**
     * @var int
     */
    protected $classMetadataCount = 1;

    /**
     * @var int
     */
    protected $constantMetadataCount = 1;

    /**
     * @var int
     */
    protected $propertyMetadataCount = 1;

    /**
     * @var int
     */
    protected $methodMetadataCount = 1;

    /**
     * @var int
     */
    protected $methodParameterMetadataCount = 1;

    /**
     * @var int
     */
    protected $functionMetadataCount = 1;

    /**
     * @var int
     */
    protected $functionParameterMetadataCount = 1;

    public function testClassMetadataCount(): void
    {
        $this->assertCount($this->classMetadataCount,
            $this->getClassMetadata(AnnotatedClass::class)
        );
    }

    public function testClassMetadataObjects(): void
    {
        $expected = $this->newAnnotation(ClassAnnotation::class, [
            'field' => 'value'
        ]);

        if ($this->classMetadataCount === 0) {
            $this->expectNotToPerformAssertions();
        }

        foreach ($this->getClassMetadata(AnnotatedClass::class) as $actual) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function testConstantMetadataCount(): void
    {
        $this->assertCount($this->constantMetadataCount,
            $this->getConstantMetadata(AnnotatedClass::class, 'CONSTANT')
        );
    }

    public function testConstantMetadataObjects(): void
    {
        $expected = $this->newAnnotation(ConstantAnnotation::class, [
            'field' => 'value'
        ]);

        if ($this->constantMetadataCount === 0) {
            $this->expectNotToPerformAssertions();
        }

        foreach ($this->getConstantMetadata(AnnotatedClass::class, 'CONSTANT') as $actual) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function testPropertyMetadataCount(): void
    {
        $this->assertCount($this->propertyMetadataCount,
            $this->getPropertyMetadata(AnnotatedClass::class, 'property')
        );
    }

    public function testPropertyMetadataObjects(): void
    {
        $expected = $this->newAnnotation(PropertyAnnotation::class, [
            'field' => 'value'
        ]);

        if ($this->propertyMetadataCount === 0) {
            $this->expectNotToPerformAssertions();
        }

        foreach ($this->getPropertyMetadata(AnnotatedClass::class, 'property') as $actual) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function testMethodMetadataCount(): void
    {
        $this->assertCount($this->methodMetadataCount,
            $this->getMethodMetadata(AnnotatedClass::class, 'method')
        );
    }

    public function testMethodMetadataObjects(): void
    {
        $expected = $this->newAnnotation(MethodAnnotation::class, [
            'field' => 'value'
        ]);

        if ($this->methodMetadataCount === 0) {
            $this->expectNotToPerformAssertions();
        }

        foreach ($this->getMethodMetadata(AnnotatedClass::class, 'method') as $actual) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function testMethodParameterMetadataCount(): void
    {
        $this->assertCount($this->methodParameterMetadataCount,
            $this->getMethodParameterMetadata(AnnotatedClass::class, 'method', 'parameter')
        );
    }

    public function testMethodParameterMetadataObjects(): void
    {
        $expected = $this->newAnnotation(MethodParameterAnnotation::class, [
            'field' => 'value'
        ]);

        if ($this->methodParameterMetadataCount === 0) {
            $this->expectNotToPerformAssertions();
        }

        foreach ($this->getMethodParameterMetadata(AnnotatedClass::class, 'method', 'parameter') as $actual) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function testFunctionMetadataCount(): void
    {
        $function = '\Spiral\Tests\Attributes\Fixture\annotated_function';

        $this->assertCount($this->functionMetadataCount,
            $this->getFunctionMetadata($function)
        );
    }

    public function testFunctionMetadataObjects(): void
    {
        $expected = $this->newAnnotation(FunctionAnnotation::class, [
            'field' => 'value'
        ]);

        if ($this->functionMetadataCount === 0) {
            $this->expectNotToPerformAssertions();
        }

        $function = '\Spiral\Tests\Attributes\Fixture\annotated_function';

        foreach ($this->getFunctionMetadata($function) as $actual) {
            $this->assertEquals($expected, $actual);
        }
    }

    public function testFunctionParameterMetadataCount(): void
    {
        $function = '\Spiral\Tests\Attributes\Fixture\annotated_function';

        $this->assertCount($this->functionParameterMetadataCount,
            $this->getFunctionParameterMetadata($function, 'parameter')
        );
    }

    public function testFunctionParameterMetadataObjects(): void
    {
        $expected = $this->newAnnotation(FunctionParameterAnnotation::class, [
            'field' => 'value'
        ]);

        if ($this->functionParameterMetadataCount === 0) {
            $this->expectNotToPerformAssertions();
        }

        $function = '\Spiral\Tests\Attributes\Fixture\annotated_function';

        foreach ($this->getFunctionParameterMetadata($function, 'parameter') as $actual) {
            $this->assertEquals($expected, $actual);
        }
    }
}
