<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions\Renderer;

use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\HtmlRenderer;
use Spiral\Exceptions\Renderer\JsonRenderer;
use Spiral\Exceptions\Renderer\PlainRenderer;

class HandlerTest extends TestCase
{
    // public function testGetMessage(): void
    // {
    //     $handler = new ConsoleHandler();
    //
    //     $this->assertStringContainsString('Error', $handler->getMessage(new Error(
    //         'message',
    //         100,
    //         __FILE__,
    //         __LINE__
    //     )));
    //
    //     $this->assertStringContainsString('message', $handler->getMessage(new Error(
    //         'message',
    //         100,
    //         __FILE__,
    //         __LINE__
    //     )));
    //
    //     $this->assertStringContainsString(__FILE__, $handler->getMessage(new Error(
    //         'message',
    //         100,
    //         __FILE__,
    //         __LINE__
    //     )));
    //
    //     $this->assertStringContainsString('100', $handler->getMessage(new Error(
    //         'message',
    //         100,
    //         __FILE__,
    //         100
    //     )));
    // }

    public function testConsoleHandlerWithoutColorsBasic(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(false);

        $result = $handler->render(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), \Spiral\Exceptions\Verbosity::BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testConsoleHandlerErrorBasic(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);
        $result = $handler->render(new \Error('message', 100), \Spiral\Exceptions\Verbosity::BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testConsoleHandlerErrorVerbose(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);
        $result = $handler->render(new \Error('message', 100), \Spiral\Exceptions\Verbosity::VERBOSE);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }


    public function testConsoleHandlerWithColorsBasic(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);

        $result = $handler->render(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), \Spiral\Exceptions\Verbosity::BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testHtmlHandlerDefaultBasic(): void
    {
        $handler = new HtmlRenderer(HtmlRenderer::DEFAULT);

        $result = $handler->render(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), \Spiral\Exceptions\Verbosity::BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testHtmlHandlerInvertedBasic(): void
    {
        $handler = new HtmlRenderer(HtmlRenderer::INVERTED);

        $result = $handler->render(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), \Spiral\Exceptions\Verbosity::BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testConsoleHandlerWithColorsDebug(): void
    {
        $handler = new ConsoleRenderer();
        $handler->setColorsSupport(true);

        $result = $handler->render(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testHtmlHandlerDefaultDebug(): void
    {
        $this->markTestSkipped('FIXME: Very long execution time');

        $handler = new HtmlRenderer(HtmlRenderer::DEFAULT);

        $result = $handler->render(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testHtmlHandlerInvertedDebug(): void
    {
        $this->markTestSkipped('FIXME: Very long execution time');

        $handler = new HtmlRenderer(HtmlRenderer::INVERTED);

        $result = $handler->render(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testConsoleHandlerStacktrace(): void
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


    public function testPlainHandlerStacktrace(): void
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

    public function testJsonHandler(): void
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

    public function testHtmlHandlerStacktrace(): void
    {
        $this->markTestSkipped('FIXME: Very long execution time');

        $handler = new HtmlRenderer(HtmlRenderer::DEFAULT);

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->render($e, \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('RuntimeException', $result);
        $this->assertStringContainsString('LogicException', $result);
        $this->assertStringContainsString('makeException', $result);
    }

    public function testHtmlHandlerInvertedStacktrace(): void
    {
        $this->markTestSkipped('FIXME: Very long execution time');

        $handler = new HtmlRenderer(HtmlRenderer::INVERTED);

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->render($e, \Spiral\Exceptions\Verbosity::DEBUG);

        $this->assertStringContainsString('RuntimeException', $result);
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
