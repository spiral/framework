<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions\Renderer;

use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\JsonRenderer;
use Spiral\Exceptions\Renderer\PlainRenderer;

class RendererTest extends TestCase
{
    public function testGetMessage(): void
    {
        $handler = new ConsoleRenderer();

        $this->assertStringContainsString('Error', $handler->render(new \Error(
            'message',
            100,
        )));

        $this->assertStringContainsString('message', $handler->render(new \Error(
            'message',
            100,
        )));

        $this->assertStringContainsString('RendererTest.php', $handler->render(new \Error(
            'message',
            100,
        )));
    }

    public function testConsoleRendererWithoutColorsBasic(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(false);

        $result = $handler->render(new \Error(
            'message',
            100,
        ), \Spiral\Exceptions\Verbosity::BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(
            'src/Exceptions/tests/Renderer/RendererTest.php',
            \str_replace('\\', '/', $result),
        );
    }

    public function testConsoleRendererErrorBasic(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);
        $result = $handler->render(new \Error('message', 100), \Spiral\Exceptions\Verbosity::BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(
            'src/Exceptions/tests/Renderer/RendererTest.php',
            \str_replace('\\', '/', $result),
        );
    }

    public function testConsoleRendererErrorVerbose(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);
        $result = $handler->render(new \Error('message', 100), \Spiral\Exceptions\Verbosity::VERBOSE);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(
            'src/Exceptions/tests/Renderer/RendererTest.php',
            \str_replace('\\', '/', $result),
        );
    }

    public function testConsoleRendererWithColorsBasic(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);

        $result = $handler->render(new \Error(
            'message',
            100,
        ), \Spiral\Exceptions\Verbosity::BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(
            'src/Exceptions/tests/Renderer/RendererTest.php',
            \str_replace('\\', '/', $result),
        );
    }

    public function testConsoleRendererWithColorsDebug(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);

        $result = $handler->render(new \Error(
            'message',
            100,
        ), \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(
            'src/Exceptions/tests/Renderer/RendererTest.php',
            \str_replace('\\', '/', $result),
        );
    }

    public function testConsoleRendererStacktrace(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->render($e, \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('LogicException', $result);
        $this->assertStringContainsString('makeException', $result);
    }

    public function testPlainRendererStacktrace(): void
    {
        $handler = new PlainRenderer();

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->render($e, \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('LogicException', $result);
        $this->assertStringContainsString('makeException', $result);
    }

    public function testJsonRenderer(): void
    {
        $handler = new JsonRenderer();

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->render($e, \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('LogicException', $result);
        $this->assertStringContainsString('makeException', $result);
    }

    public function makeException(): void
    {
        try {
            $f = function (): void {
                throw new \RuntimeException('error');
            };

            $f();
        } catch (\Throwable $e) {
            throw new \LogicException('error', 0, $e);
        }
    }
}
