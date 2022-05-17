<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\EnumCase as NetteEnumCase;
use Spiral\Reactor\Partial\Attribute;
use Spiral\Reactor\Partial\EnumCase;

final class EnumCaseTest extends TestCase
{
    public function testGetName(): void
    {
        $case = new EnumCase('test');

        $this->assertSame('test', $case->getName());
    }

    public function testComment(): void
    {
        $case = new EnumCase('test');
        $this->assertNull($case->getComment());

        $case->setComment('/** Awesome case */');
        $this->assertSame('/** Awesome case */', $case->getComment());

        $case->setComment(null);
        $this->assertNull($case->getComment());

        $case->setComment(['/** Line one */', '/** Line two */']);
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $case->getComment())
        );

        $case->setComment(null);
        $case->addComment('/** Line one */');
        $case->addComment('/** Line two */');
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $case->getComment())
        );
    }

    public function testAttribute(): void
    {
        $case = new EnumCase('test');
        $this->assertEmpty($case->getAttributes());

        $case->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        $this->assertCount(1, $case->getAttributes());

        $case->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar'])
        ]);
        $this->assertCount(2, $case->getAttributes());
    }

    public function testFromElement(): void
    {
        $case = EnumCase::fromElement(new NetteEnumCase('test'));

        $this->assertInstanceOf(EnumCase::class, $case);
        $this->assertSame('test', $case->getName());
    }

    public function testGetElement(): void
    {
        $element = (new EnumCase('test'))->getElement();

        $this->assertInstanceOf(NetteEnumCase::class, $element);
        $this->assertSame('test', $element->getName());
    }
}
