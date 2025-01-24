<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\Constant as NetteConstant;
use Spiral\Reactor\Partial\Attribute;
use Spiral\Reactor\Partial\Constant;

final class ConstantTest extends TestCase
{
    public function testGetName(): void
    {
        $constant = new Constant('test');
        self::assertSame('TEST', $constant->getName());

        $constant = new Constant('TEST');
        self::assertSame('TEST', $constant->getName());
    }

    public function testAttribute(): void
    {
        $constant = new Constant('TEST');
        self::assertEmpty($constant->getAttributes());

        $constant->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        self::assertCount(1, $constant->getAttributes());

        $constant->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
        ]);
        self::assertCount(2, $constant->getAttributes());
    }

    public function testComment(): void
    {
        $constant = new Constant('TEST');
        self::assertNull($constant->getComment());

        $constant->setComment('/** Awesome constant */');
        self::assertSame('/** Awesome constant */', $constant->getComment());

        $constant->setComment(null);
        self::assertNull($constant->getComment());

        $constant->setComment(['/** Line one */', '/** Line two */']);
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $constant->getComment()));

        $constant->setComment(null);
        $constant->addComment('/** Line one */');
        $constant->addComment('/** Line two */');
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $constant->getComment()));
    }

    public function testValue(): void
    {
        $constant = new Constant('TEST');

        self::assertNull($constant->getValue());

        $constant->setValue('foo');
        self::assertSame('foo', $constant->getValue());
    }

    public function testFinal(): void
    {
        $constant = new Constant('TEST');

        self::assertFalse($constant->isFinal());

        $constant->setFinal();
        self::assertTrue($constant->isFinal());

        $constant->setFinal(false);
        self::assertFalse($constant->isFinal());

        $constant->setFinal(true);
        self::assertTrue($constant->isFinal());
    }

    public function testFromElement(): void
    {
        $constant = Constant::fromElement(new NetteConstant('TEST'));

        self::assertInstanceOf(Constant::class, $constant);
        self::assertSame('TEST', $constant->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Constant('TEST'))->getElement();

        self::assertInstanceOf(NetteConstant::class, $element);
        self::assertSame('TEST', $element->getName());
    }
}
