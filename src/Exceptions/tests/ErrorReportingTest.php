<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions;

use ErrorException;
use JetBrains\PhpStorm\ExpectedValues;
use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\ExceptionHandler;
use Throwable;

class ErrorReportingTest extends TestCase
{
    protected int $reportingBefore;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportingBefore = \error_reporting();
    }

    protected function tearDown(): void
    {
        \error_reporting($this->reportingBefore);
        parent::tearDown();
    }

    public function testNoticeOnly(): void
    {
        \error_reporting(\E_USER_NOTICE);

        self::assertInstanceOf(ErrorException::class, $this->handleError(\E_USER_NOTICE));
        self::assertNull($this->handleError(\E_USER_DEPRECATED));
        self::assertNull($this->handleError(\E_USER_ERROR));
        self::assertNull($this->handleError(\E_USER_WARNING));
    }

    public function testWithoutDeprecations(): void
    {
        \error_reporting(\E_ALL ^ \E_DEPRECATED);

        self::assertInstanceOf(ErrorException::class, $this->handleError(\E_USER_NOTICE));
        self::assertInstanceOf(ErrorException::class, $this->handleError(\E_USER_DEPRECATED));
        self::assertInstanceOf(ErrorException::class, $this->handleError(\E_USER_ERROR));
        self::assertInstanceOf(ErrorException::class, $this->handleError(\E_USER_WARNING));
        self::assertInstanceOf(ErrorException::class, $this->handleError(\E_NOTICE));
        self::assertInstanceOf(ErrorException::class, $this->handleError(\E_ERROR));
        self::assertNull($this->handleError(\E_DEPRECATED));
    }

    /**
     * @return array{type: int, message: string, file: string, line: int}
     */
    private function generateErrorArray(
        #[ExpectedValues(values: [\E_USER_NOTICE, \E_USER_WARNING, \E_USER_ERROR, \E_USER_DEPRECATED, \E_DEPRECATED])]
        int $type
    ): array {
        return [
            'type' => $type,
            'message' => 'Foo bar',
            'file' => 'PHP26C0.tmp',
            'line' => 103,
        ];
    }

    private function handleError(
        #[ExpectedValues(values: [\E_USER_NOTICE, \E_USER_WARNING, \E_USER_ERROR])]
        int $type
    ): ?Throwable {
        $handler = (new class extends ExceptionHandler {
            public function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): bool
            {
                return parent::handleError($errno, $errstr, $errfile, $errline);
            }

            protected function bootBasicHandlers(): void
            {
            }
        });
        try {
            $handler->handleError(...\array_values($this->generateErrorArray($type)));
        } catch (Throwable $e) {
            return $e;
        }
        return null;
    }
}
