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
        $this->assertNull($use->getComment());

        $use->setComment('/** Awesome comment */');
        $this->assertSame('/** Awesome comment */', $use->getComment());

        $use->setComment(null);
        $this->assertNull($use->getComment());

        $use->setComment(['/** Line one */', '/** Line two */']);
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $use->getComment())
        );

        $use->setComment(null);
        $use->addComment('/** Line one */');
        $use->addComment('/** Line two */');
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $use->getComment())
        );
    }

    public function testGetName(): void
    {
        $use = new TraitUse('test');

        $this->assertSame('test', $use->getName());
    }

    public function testResolution(): void
    {
        $use = new TraitUse('test');

        $this->assertEmpty($use->getResolutions());

        $use->addResolution('foo');
        $use->addResolution('bar');
        $this->assertSame(['foo', 'bar'], $use->getResolutions());
    }

    public function testFromElement(): void
    {
        $use = TraitUse::fromElement(new NetteTraitUse('test'));

        $this->assertInstanceOf(TraitUse::class, $use);
        $this->assertSame('test', $use->getName());
    }

    public function testGetElement(): void
    {
        $element = (new TraitUse('test'))->getElement();

        $this->assertInstanceOf(NetteTraitUse::class, $element);
        $this->assertSame('test', $element->getName());
    }
}
