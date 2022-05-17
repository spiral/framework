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
        $this->assertSame('TEST', $constant->getName());

        $constant = new Constant('TEST');
        $this->assertSame('TEST', $constant->getName());
    }

    public function testAttribute(): void
    {
        $constant = new Constant('TEST');
        $this->assertEmpty($constant->getAttributes());

        $constant->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        $this->assertCount(1, $constant->getAttributes());

        $constant->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar'])
        ]);
        $this->assertCount(2, $constant->getAttributes());
    }

    public function testComment(): void
    {
        $constant = new Constant('TEST');
        $this->assertNull($constant->getComment());

        $constant->setComment('/** Awesome constant */');
        $this->assertSame('/** Awesome constant */', $constant->getComment());

        $constant->setComment(null);
        $this->assertNull($constant->getComment());

        $constant->setComment(['/** Line one */', '/** Line two */']);
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $constant->getComment())
        );

        $constant->setComment(null);
        $constant->addComment('/** Line one */');
        $constant->addComment('/** Line two */');
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $constant->getComment())
        );
    }

    public function testValue(): void
    {
        $constant = new Constant('TEST');

        $this->assertNull($constant->getValue());

        $constant->setValue('foo');
        $this->assertSame('foo', $constant->getValue());
    }

    public function testFinal(): void
    {
        $constant = new Constant('TEST');

        $this->assertFalse($constant->isFinal());

        $constant->setFinal();
        $this->assertTrue($constant->isFinal());

        $constant->setFinal(false);
        $this->assertFalse($constant->isFinal());

        $constant->setFinal(true);
        $this->assertTrue($constant->isFinal());
    }

    public function testFromElement(): void
    {
        $constant = Constant::fromElement(new NetteConstant('TEST'));

        $this->assertInstanceOf(Constant::class, $constant);
        $this->assertSame('TEST', $constant->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Constant('TEST'))->getElement();

        $this->assertInstanceOf(NetteConstant::class, $element);
        $this->assertSame('TEST', $element->getName());
    }
}
