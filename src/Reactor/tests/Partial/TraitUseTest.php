<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\TraitUse as NetteTraitUse;
use Spiral\Reactor\Partial\TraitUse;

final class TraitUseTest extends TestCase
{
    public function testComment(): void
    {
        $use = new TraitUse('test');
        self::assertNull($use->getComment());

        $use->setComment('/** Awesome comment */');
        self::assertSame('/** Awesome comment */', $use->getComment());

        $use->setComment(null);
        self::assertNull($use->getComment());

        $use->setComment(['/** Line one */', '/** Line two */']);
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $use->getComment()));

        $use->setComment(null);
        $use->addComment('/** Line one */');
        $use->addComment('/** Line two */');
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $use->getComment()));
    }

    public function testGetName(): void
    {
        $use = new TraitUse('test');

        self::assertSame('test', $use->getName());
    }

    public function testResolution(): void
    {
        $use = new TraitUse('test');

        self::assertEmpty($use->getResolutions());

        $use->addResolution('foo');
        $use->addResolution('bar');
        self::assertSame(['foo', 'bar'], $use->getResolutions());
    }

    public function testFromElement(): void
    {
        $use = TraitUse::fromElement(new NetteTraitUse('test'));

        self::assertInstanceOf(TraitUse::class, $use);
        self::assertSame('test', $use->getName());
    }

    public function testGetElement(): void
    {
        $element = (new TraitUse('test'))->getElement();

        self::assertInstanceOf(NetteTraitUse::class, $element);
        self::assertSame('test', $element->getName());
    }
}
