<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Compiler;

use Spiral\Stempler\Compiler\Renderer\CoreRenderer;

class RawTest extends BaseTest
{
    protected const RENDERS = [
        CoreRenderer::class,
    ];

    public function testCompileRaw(): void
    {
        $doc = $this->parse('hello world');

        $this->assertSame('hello world', $this->compile($doc));
    }
}
