<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions\Reporter;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Core\Container;
use Spiral\Exceptions\ExceptionHandler;
use Spiral\Exceptions\Reporter\LoggerReporter;

final class LoggerReporterTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testReport(): void
    {
        $exception = new \Exception();

        $container = new Container();

        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->withArgs([\sprintf(
            '%s: %s in %s at line %s',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        )])->once();
        $container->bind(LoggerInterface::class, $logger);

        $handler = new class extends ExceptionHandler {
            protected function bootBasicHandlers(): void
            {
            }
        };

        $handler->addReporter(new LoggerReporter($container));

        $handler->report($exception);

        $this->assertTrue(true);
    }

    public function testReportWithoutLogger(): void
    {
        $handler = new class extends ExceptionHandler {
            protected function bootBasicHandlers(): void
            {
            }
        };

        $handler->addReporter(new LoggerReporter(new Container()));

        $handler->report(new \Exception());

        // any errors
        $this->assertTrue(true);
    }
}
