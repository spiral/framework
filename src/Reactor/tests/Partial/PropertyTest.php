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
        self::assertEmpty($property->getAttributes());

        $property->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        self::assertCount(1, $property->getAttributes());

        $property->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
        ]);
        self::assertCount(2, $property->getAttributes());
    }

    public function testComment(): void
    {
        $property = new Property('foo');
        self::assertNull($property->getComment());

        $property->setComment('/** Awesome property */');
        self::assertSame('/** Awesome property */', $property->getComment());

        $property->setComment(null);
        self::assertNull($property->getComment());

        $property->setComment(['/** Line one */', '/** Line two */']);
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $property->getComment()));

        $property->setComment(null);
        $property->addComment('/** Line one */');
        $property->addComment('/** Line two */');
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $property->getComment()));
    }

    public function testGetName(): void
    {
        $property = new Property('foo');

        self::assertSame('foo', $property->getName());
    }

    public function testVisibility(): void
    {
        $property = new Property('foo');
        self::assertNull($property->getVisibility());

        $property->setVisibility(Visibility::PUBLIC);
        self::assertSame(Visibility::PUBLIC, $property->getVisibility());
        self::assertTrue($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertFalse($property->isPrivate());

        $property->setVisibility(Visibility::PROTECTED);
        self::assertSame(Visibility::PROTECTED, $property->getVisibility());
        self::assertFalse($property->isPublic());
        self::assertTrue($property->isProtected());
        self::assertFalse($property->isPrivate());

        $property->setVisibility(Visibility::PRIVATE);
        self::assertSame(Visibility::PRIVATE, $property->getVisibility());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertTrue($property->isPrivate());

        $property->setPublic();
        self::assertSame(Visibility::PUBLIC, $property->getVisibility());
        self::assertTrue($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertFalse($property->isPrivate());

        $property->setProtected();
        self::assertSame(Visibility::PROTECTED, $property->getVisibility());
        self::assertFalse($property->isPublic());
        self::assertTrue($property->isProtected());
        self::assertFalse($property->isPrivate());

        $property->setPrivate();
        self::assertSame(Visibility::PRIVATE, $property->getVisibility());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertTrue($property->isPrivate());
    }

    public function testValue(): void
    {
        $property = new Property('foo');

        self::assertNull($property->getValue());

        $property->setValue('foo');
        self::assertSame('foo', $property->getValue());
    }

    public function testStatic(): void
    {
        $property = new Property('foo');

        self::assertFalse($property->isStatic());

        $property->setStatic(true);
        self::assertTrue($property->isStatic());

        $property->setStatic(false);
        self::assertFalse($property->isStatic());

        $property->setStatic(true);
        self::assertTrue($property->isStatic());
    }

    public function testType(): void
    {
        $property = new Property('foo');
        self::assertNull($property->getType());

        $property->setType('int');
        self::assertSame('int', $property->getType());
    }

    public function testNullable(): void
    {
        $property = new Property('foo');
        self::assertFalse($property->isNullable());

        $property->setNullable(true);
        self::assertTrue($property->isNullable());

        $property->setNullable(false);
        self::assertFalse($property->isNullable());
    }

    public function testInitialized(): void
    {
        $property = new Property('foo');
        self::assertFalse($property->isInitialized());

        $property->setValue(null);
        self::assertTrue($property->isInitialized());

        $property = new Property('foo');

        $property->setValue('bar');
        self::assertTrue($property->isInitialized());
    }

    public function testReadOnly(): void
    {
        $property = new Property('foo');

        self::assertFalse($property->isReadOnly());

        $property->setReadOnly(true);
        self::assertTrue($property->isReadOnly());

        $property->setReadOnly(false);
        self::assertFalse($property->isReadOnly());

        $property->setReadOnly(true);
        self::assertTrue($property->isReadOnly());
    }

    public function testFromElement(): void
    {
        $property = Property::fromElement(new NetteProperty('test'));

        self::assertInstanceOf(Property::class, $property);
        self::assertSame('test', $property->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Property('test'))->getElement();

        self::assertInstanceOf(NetteProperty::class, $element);
        self::assertSame('test', $element->getName());
    }
}
