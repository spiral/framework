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

        self::assertSame('test', $case->getName());
    }

    public function testComment(): void
    {
        $case = new EnumCase('test');
        self::assertNull($case->getComment());

        $case->setComment('/** Awesome case */');
        self::assertSame('/** Awesome case */', $case->getComment());

        $case->setComment(null);
        self::assertNull($case->getComment());

        $case->setComment(['/** Line one */', '/** Line two */']);
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $case->getComment()));

        $case->setComment(null);
        $case->addComment('/** Line one */');
        $case->addComment('/** Line two */');
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $case->getComment()));
    }

    public function testAttribute(): void
    {
        $case = new EnumCase('test');
        self::assertEmpty($case->getAttributes());

        $case->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        self::assertCount(1, $case->getAttributes());

        $case->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
        ]);
        self::assertCount(2, $case->getAttributes());
    }

    public function testFromElement(): void
    {
        $case = EnumCase::fromElement(new NetteEnumCase('test'));

        self::assertInstanceOf(EnumCase::class, $case);
        self::assertSame('test', $case->getName());
    }

    public function testGetElement(): void
    {
        $element = (new EnumCase('test'))->getElement();

        self::assertInstanceOf(NetteEnumCase::class, $element);
        self::assertSame('test', $element->getName());
    }
}
