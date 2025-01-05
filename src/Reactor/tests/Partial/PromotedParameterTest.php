<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Spiral\Reactor\Partial\PromotedParameter;
use Spiral\Reactor\Partial\Visibility;

final class PromotedParameterTest extends TestCase
{
    public function testComment(): void
    {
        $param = new PromotedParameter('test');
        self::assertNull($param->getComment());

        $param->setComment('/** Awesome case */');
        self::assertSame('/** Awesome case */', $param->getComment());

        $param->setComment(null);
        self::assertNull($param->getComment());

        $param->setComment(['/** Line one */', '/** Line two */']);
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $param->getComment()));

        $param->setComment(null);
        $param->addComment('/** Line one */');
        $param->addComment('/** Line two */');
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $param->getComment()));
    }

    public function testVisibility(): void
    {
        $param = new PromotedParameter('test');
        self::assertNull($param->getVisibility());

        $param->setVisibility(Visibility::PUBLIC);
        self::assertSame(Visibility::PUBLIC, $param->getVisibility());
        self::assertTrue($param->isPublic());
        self::assertFalse($param->isProtected());
        self::assertFalse($param->isPrivate());

        $param->setVisibility(Visibility::PROTECTED);
        self::assertSame(Visibility::PROTECTED, $param->getVisibility());
        self::assertFalse($param->isPublic());
        self::assertTrue($param->isProtected());
        self::assertFalse($param->isPrivate());

        $param->setVisibility(Visibility::PRIVATE);
        self::assertSame(Visibility::PRIVATE, $param->getVisibility());
        self::assertFalse($param->isPublic());
        self::assertFalse($param->isProtected());
        self::assertTrue($param->isPrivate());

        $param->setPublic();
        self::assertSame(Visibility::PUBLIC, $param->getVisibility());
        self::assertTrue($param->isPublic());
        self::assertFalse($param->isProtected());
        self::assertFalse($param->isPrivate());

        $param->setProtected();
        self::assertSame(Visibility::PROTECTED, $param->getVisibility());
        self::assertFalse($param->isPublic());
        self::assertTrue($param->isProtected());
        self::assertFalse($param->isPrivate());

        $param->setPrivate();
        self::assertSame(Visibility::PRIVATE, $param->getVisibility());
        self::assertFalse($param->isPublic());
        self::assertFalse($param->isProtected());
        self::assertTrue($param->isPrivate());
    }

    public function testReadOnly(): void
    {
        $param = new PromotedParameter('test');

        self::assertFalse($param->isReadOnly());

        $param->setReadOnly(true);
        self::assertTrue($param->isReadOnly());

        $param->setReadOnly(false);
        self::assertFalse($param->isReadOnly());

        $param->setReadOnly(true);
        self::assertTrue($param->isReadOnly());
    }
}
