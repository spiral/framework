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
        $this->assertNull($param->getComment());

        $param->setComment('/** Awesome case */');
        $this->assertSame('/** Awesome case */', $param->getComment());

        $param->setComment(null);
        $this->assertNull($param->getComment());

        $param->setComment(['/** Line one */', '/** Line two */']);
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $param->getComment())
        );

        $param->setComment(null);
        $param->addComment('/** Line one */');
        $param->addComment('/** Line two */');
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $param->getComment())
        );
    }

    public function testVisibility(): void
    {
        $param = new PromotedParameter('test');
        $this->assertNull($param->getVisibility());

        $param->setVisibility(Visibility::PUBLIC);
        $this->assertSame(Visibility::PUBLIC, $param->getVisibility());
        $this->assertTrue($param->isPublic());
        $this->assertFalse($param->isProtected());
        $this->assertFalse($param->isPrivate());

        $param->setVisibility(Visibility::PROTECTED);
        $this->assertSame(Visibility::PROTECTED, $param->getVisibility());
        $this->assertFalse($param->isPublic());
        $this->assertTrue($param->isProtected());
        $this->assertFalse($param->isPrivate());

        $param->setVisibility(Visibility::PRIVATE);
        $this->assertSame(Visibility::PRIVATE, $param->getVisibility());
        $this->assertFalse($param->isPublic());
        $this->assertFalse($param->isProtected());
        $this->assertTrue($param->isPrivate());

        $param->setPublic();
        $this->assertSame(Visibility::PUBLIC, $param->getVisibility());
        $this->assertTrue($param->isPublic());
        $this->assertFalse($param->isProtected());
        $this->assertFalse($param->isPrivate());

        $param->setProtected();
        $this->assertSame(Visibility::PROTECTED, $param->getVisibility());
        $this->assertFalse($param->isPublic());
        $this->assertTrue($param->isProtected());
        $this->assertFalse($param->isPrivate());

        $param->setPrivate();
        $this->assertSame(Visibility::PRIVATE, $param->getVisibility());
        $this->assertFalse($param->isPublic());
        $this->assertFalse($param->isProtected());
        $this->assertTrue($param->isPrivate());
    }

    public function testReadOnly(): void
    {
        $param = new PromotedParameter('test');

        $this->assertFalse($param->isReadOnly());

        $param->setReadOnly(true);
        $this->assertTrue($param->isReadOnly());

        $param->setReadOnly(false);
        $this->assertFalse($param->isReadOnly());

        $param->setReadOnly(true);
        $this->assertTrue($param->isReadOnly());
    }
}
