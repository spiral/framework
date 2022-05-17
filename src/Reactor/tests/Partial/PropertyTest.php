<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\Property as NetteProperty;
use Spiral\Reactor\Partial\Attribute;
use Spiral\Reactor\Partial\Property;
use Spiral\Reactor\Partial\Visibility;

final class PropertyTest extends TestCase
{
    public function testAttribute(): void
    {
        $property = new Property('foo');
        $this->assertEmpty($property->getAttributes());

        $property->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        $this->assertCount(1, $property->getAttributes());

        $property->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar'])
        ]);
        $this->assertCount(2, $property->getAttributes());
    }

    public function testComment(): void
    {
        $property = new Property('foo');
        $this->assertNull($property->getComment());

        $property->setComment('/** Awesome property */');
        $this->assertSame('/** Awesome property */', $property->getComment());

        $property->setComment(null);
        $this->assertNull($property->getComment());

        $property->setComment(['/** Line one */', '/** Line two */']);
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $property->getComment())
        );

        $property->setComment(null);
        $property->addComment('/** Line one */');
        $property->addComment('/** Line two */');
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $property->getComment())
        );
    }

    public function testGetName(): void
    {
        $property = new Property('foo');

        $this->assertSame('foo', $property->getName());
    }

    public function testVisibility(): void
    {
        $property = new Property('foo');
        $this->assertNull($property->getVisibility());

        $property->setVisibility(Visibility::PUBLIC);
        $this->assertSame(Visibility::PUBLIC, $property->getVisibility());
        $this->assertTrue($property->isPublic());
        $this->assertFalse($property->isProtected());
        $this->assertFalse($property->isPrivate());

        $property->setVisibility(Visibility::PROTECTED);
        $this->assertSame(Visibility::PROTECTED, $property->getVisibility());
        $this->assertFalse($property->isPublic());
        $this->assertTrue($property->isProtected());
        $this->assertFalse($property->isPrivate());

        $property->setVisibility(Visibility::PRIVATE);
        $this->assertSame(Visibility::PRIVATE, $property->getVisibility());
        $this->assertFalse($property->isPublic());
        $this->assertFalse($property->isProtected());
        $this->assertTrue($property->isPrivate());

        $property->setPublic();
        $this->assertSame(Visibility::PUBLIC, $property->getVisibility());
        $this->assertTrue($property->isPublic());
        $this->assertFalse($property->isProtected());
        $this->assertFalse($property->isPrivate());

        $property->setProtected();
        $this->assertSame(Visibility::PROTECTED, $property->getVisibility());
        $this->assertFalse($property->isPublic());
        $this->assertTrue($property->isProtected());
        $this->assertFalse($property->isPrivate());

        $property->setPrivate();
        $this->assertSame(Visibility::PRIVATE, $property->getVisibility());
        $this->assertFalse($property->isPublic());
        $this->assertFalse($property->isProtected());
        $this->assertTrue($property->isPrivate());
    }

    public function testValue(): void
    {
        $property = new Property('foo');

        $this->assertNull($property->getValue());

        $property->setValue('foo');
        $this->assertSame('foo', $property->getValue());
    }

    public function testStatic(): void
    {
        $property = new Property('foo');

        $this->assertFalse($property->isStatic());

        $property->setStatic(true);
        $this->assertTrue($property->isStatic());

        $property->setStatic(false);
        $this->assertFalse($property->isStatic());

        $property->setStatic(true);
        $this->assertTrue($property->isStatic());
    }

    public function testType(): void
    {
        $property = new Property('foo');
        $this->assertNull($property->getType());

        $property->setType('int');
        $this->assertSame('int', $property->getType());
    }

    public function testNullable(): void
    {
        $property = new Property('foo');
        $this->assertFalse($property->isNullable());

        $property->setNullable(true);
        $this->assertTrue($property->isNullable());

        $property->setNullable(false);
        $this->assertFalse($property->isNullable());
    }

    public function testInitialized(): void
    {
        $property = new Property('foo');
        $this->assertFalse($property->isInitialized());

        $property->setValue(null);
        $this->assertTrue($property->isInitialized());

        $property = new Property('foo');

        $property->setValue('bar');
        $this->assertTrue($property->isInitialized());
    }

    public function testReadOnly(): void
    {
        $property = new Property('foo');

        $this->assertFalse($property->isReadOnly());

        $property->setReadOnly(true);
        $this->assertTrue($property->isReadOnly());

        $property->setReadOnly(false);
        $this->assertFalse($property->isReadOnly());

        $property->setReadOnly(true);
        $this->assertTrue($property->isReadOnly());
    }

    public function testFromElement(): void
    {
        $property = Property::fromElement(new NetteProperty('test'));

        $this->assertInstanceOf(Property::class, $property);
        $this->assertSame('test', $property->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Property('test'))->getElement();

        $this->assertInstanceOf(NetteProperty::class, $element);
        $this->assertSame('test', $element->getName());
    }
}
