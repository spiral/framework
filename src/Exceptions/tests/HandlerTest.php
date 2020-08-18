<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Debug;

use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\ConsoleHandler;
use Spiral\Exceptions\HandlerInterface;
use Spiral\Exceptions\HtmlHandler;
use Spiral\Exceptions\JsonHandler;
use Spiral\Exceptions\PlainHandler;

class HandlerTest extends TestCase
{
    public function testGetMessage(): void
    {
        $handler = new ConsoleHandler();

        $this->assertStringContainsString('Error', $handler->getMessage(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        )));

        $this->assertStringContainsString('message', $handler->getMessage(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        )));

        $this->assertStringContainsString(__FILE__, $handler->getMessage(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        )));

        $this->assertStringContainsString('100', $handler->getMessage(new Error(
            'message',
            100,
            __FILE__,
            100
        )));
    }

    public function testConsoleHandlerWithoutColorsBasic(): void
    {
        $handler = new ConsoleHandler();
        $handler->setColorsSupport(false);

        $result = $handler->renderException(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), HandlerInterface::VERBOSITY_BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testConsoleHandlerErrorBasic(): void
    {
        $handler = new ConsoleHandler();
        $handler->setColorsSupport(true);
        $result = $handler->renderException(new \Error('message', 100), HandlerInterface::VERBOSITY_BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testConsoleHandlerErrorVerbose(): void
    {
        $handler = new ConsoleHandler();
        $handler->setColorsSupport(true);
        $result = $handler->renderException(new \Error('message', 100), HandlerInterface::VERBOSITY_VERBOSE);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }


    public function testConsoleHandlerWithColorsBasic(): void
    {
        $handler = new ConsoleHandler();
        $handler->setColorsSupport(true);

        $result = $handler->renderException(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), HandlerInterface::VERBOSITY_BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testHtmlHandlerDefaultBasic(): void
    {
        $handler = new HtmlHandler(HtmlHandler::DEFAULT);

        $result = $handler->renderException(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), HandlerInterface::VERBOSITY_BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testHtmlHandlerInvertedBasic(): void
    {
        $handler = new HtmlHandler(HtmlHandler::INVERTED);

        $result = $handler->renderException(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), HandlerInterface::VERBOSITY_BASIC);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testConsoleHandlerWithColorsDebug(): void
    {
        $handler = new ConsoleHandler();
        $handler->setColorsSupport(true);

        $result = $handler->renderException(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testHtmlHandlerDefaultDebug(): void
    {
        $this->markTestSkipped('FIXME: Very long execution time');

        $handler = new HtmlHandler(HtmlHandler::DEFAULT);

        $result = $handler->renderException(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testHtmlHandlerInvertedDebug(): void
    {
        $this->markTestSkipped('FIXME: Very long execution time');

        $handler = new HtmlHandler(HtmlHandler::INVERTED);

        $result = $handler->renderException(new Error(
            'message',
            100,
            __FILE__,
            __LINE__
        ), HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('Error', $result);
        $this->assertStringContainsString('message', $result);
        $this->assertStringContainsString(__FILE__, $result);
    }

    public function testConsoleHandlerStacktrace(): void
    {
        $handler = new ConsoleHandler();
        $handler->setColorsSupport(true);

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->renderException($e, HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('LogicException', $result);
        $this->assertStringContainsString('makeException', $result);
    }


    public function testPlainHandlerStacktrace(): void
    {
        $handler = new PlainHandler();

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->renderException($e, HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('LogicException', $result);
        $this->assertStringContainsString('makeException', $result);
    }

    public function testJsonHandler(): void
    {
        $handler = new JsonHandler();

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->renderException($e, HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('LogicException', $result);
        $this->assertStringContainsString('makeException', $result);
    }

    public function testHtmlHandlerStacktrace(): void
    {
        $handler = new HtmlHandler(HtmlHandler::DEFAULT);

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->renderException($e, HandlerInterface::VERBOSITY_DEBUG);

        $this->assertStringContainsString('RuntimeException', $result);
        $this->assertStringContainsString('LogicException', $result);
        $this->assertStringContainsString('makeException', $result);
    }

    public function testHtmlHandlerInvertedStacktrace(): void
    {
        $handler = new HtmlHandler(HtmlHandler::INVERTED);

        try {
            $this->makeException();
        } catch (\Throwable $e) {
        }

        $result = $handler->renderException($e, HandlerInterface::VERBOSITY_DEBUG);

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
