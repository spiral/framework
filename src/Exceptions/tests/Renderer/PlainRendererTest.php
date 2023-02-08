<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions\Renderer;

use Exception;
use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\Renderer\PlainRenderer;

class PlainRendererTest extends TestCase
{
    public function testRenderException(): void
    {
        $plainHandler = new PlainRenderer();
        $result = $plainHandler->render(new Exception('Undefined variable $undefined'));

        $this->assertStringContainsString(
            'Undefined variable $undefined',
            $result
        );
    }
}
