<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\Attribute as NetteAttribute;
use Spiral\Reactor\Partial\Attribute;

final class AttributeTest extends TestCase
{
    public function testGetArguments(): void
    {
        $attribute = new Attribute('test', ['name' => 'foo', 'otherName' => 'bar']);

        self::assertSame(['name' => 'foo', 'otherName' => 'bar'], $attribute->getArguments());
    }

    public function testGetName(): void
    {
        $attribute = new Attribute('test', []);

        self::assertSame('test', $attribute->getName());
    }

    public function testFromElement(): void
    {
        $attribute = Attribute::fromElement(new NetteAttribute('test', []));

        self::assertInstanceOf(Attribute::class, $attribute);
        self::assertSame('test', $attribute->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Attribute('test', []))->getElement();

        self::assertInstanceOf(NetteAttribute::class, $element);
        self::assertSame('test', $element->getName());
    }
}
