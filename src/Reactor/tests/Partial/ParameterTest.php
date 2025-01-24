<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\Parameter as NetteParameter;
use Spiral\Reactor\Partial\Attribute;
use Spiral\Reactor\Partial\Parameter;

final class ParameterTest extends TestCase
{
    public function testAttribute(): void
    {
        $param = new Parameter('test');
        self::assertEmpty($param->getAttributes());

        $param->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        self::assertCount(1, $param->getAttributes());

        $param->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
        ]);
        self::assertCount(2, $param->getAttributes());
    }

    public function testGetName(): void
    {
        $param = new Parameter('test');

        self::assertSame('test', $param->getName());
    }

    public function testReference(): void
    {
        $param = new Parameter('test');

        self::assertFalse($param->isReference());

        $param->setReference(true);
        self::assertTrue($param->isReference());

        $param->setReference(false);
        self::assertFalse($param->isReference());

        $param->setReference(true);
        self::assertTrue($param->isReference());
    }

    public function testType(): void
    {
        $param = new Parameter('test');
        self::assertNull($param->getType());

        $param->setType('int');
        self::assertSame('int', $param->getType());
    }

    public function testNullable(): void
    {
        $param = new Parameter('test');
        self::assertFalse($param->isNullable());

        $param->setNullable(true);
        self::assertTrue($param->isNullable());

        $param->setNullable(false);
        self::assertFalse($param->isNullable());
    }

    public function testDefaultValue(): void
    {
        $param = new Parameter('test');
        self::assertFalse($param->hasDefaultValue());
        self::assertNull($param->getDefaultValue());

        $param->setDefaultValue(null);
        self::assertTrue($param->hasDefaultValue());
        self::assertNull($param->getDefaultValue());

        $param->setDefaultValue('foo');
        self::assertTrue($param->hasDefaultValue());
        self::assertSame('foo', $param->getDefaultValue());
    }

    public function testFromElement(): void
    {
        $param = Parameter::fromElement(new NetteParameter('test'));

        self::assertInstanceOf(Parameter::class, $param);
        self::assertSame('test', $param->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Parameter('test'))->getElement();

        self::assertInstanceOf(NetteParameter::class, $element);
        self::assertSame('test', $element->getName());
    }
}
