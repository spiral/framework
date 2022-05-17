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
        $this->assertEmpty($param->getAttributes());

        $param->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        $this->assertCount(1, $param->getAttributes());

        $param->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar'])
        ]);
        $this->assertCount(2, $param->getAttributes());
    }

    public function testGetName(): void
    {
        $param = new Parameter('test');

        $this->assertSame('test', $param->getName());
    }

    public function testReference(): void
    {
        $param = new Parameter('test');

        $this->assertFalse($param->isReference());

        $param->setReference(true);
        $this->assertTrue($param->isReference());

        $param->setReference(false);
        $this->assertFalse($param->isReference());

        $param->setReference(true);
        $this->assertTrue($param->isReference());
    }

    public function testType(): void
    {
        $param = new Parameter('test');
        $this->assertNull($param->getType());

        $param->setType('int');
        $this->assertSame('int', $param->getType());
    }

    public function testNullable(): void
    {
        $param = new Parameter('test');
        $this->assertFalse($param->isNullable());

        $param->setNullable(true);
        $this->assertTrue($param->isNullable());

        $param->setNullable(false);
        $this->assertFalse($param->isNullable());
    }

    public function testDefaultValue(): void
    {
        $param = new Parameter('test');
        $this->assertFalse($param->hasDefaultValue());
        $this->assertNull($param->getDefaultValue());

        $param->setDefaultValue(null);
        $this->assertTrue($param->hasDefaultValue());
        $this->assertNull($param->getDefaultValue());

        $param->setDefaultValue('foo');
        $this->assertTrue($param->hasDefaultValue());
        $this->assertSame('foo', $param->getDefaultValue());
    }

    public function testFromElement(): void
    {
        $param = Parameter::fromElement(new NetteParameter('test'));

        $this->assertInstanceOf(Parameter::class, $param);
        $this->assertSame('test', $param->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Parameter('test'))->getElement();

        $this->assertInstanceOf(NetteParameter::class, $element);
        $this->assertSame('test', $element->getName());
    }
}
