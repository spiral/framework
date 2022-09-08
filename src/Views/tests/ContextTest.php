<?php

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Views\Context\ValueDependency;
use Spiral\Views\Exception\ContextException;
use Spiral\Views\ViewContext;

class ContextTest extends TestCase
{
    public function testResolveValue(): void
    {
        $context = new ViewContext();
        $context = $context->withDependency(new ValueDependency('test', 'value'));

        $this->assertSame('value', $context->resolveValue('test'));
    }

    public function testResolveValueException(): void
    {
        $this->expectException(ContextException::class);

        $context = new ViewContext();
        $context = $context->withDependency(new ValueDependency('test', 'value'));

        $this->assertSame('value', $context->resolveValue('other'));
    }
}
