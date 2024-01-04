<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

use Closure;
use Spiral\Exceptions\Attribute\NonReportable;
use Spiral\Exceptions\Renderer\PlainRenderer;
use Spiral\Filters\Exception\AuthorizationException;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Http\Exception\ClientException\BadRequestException;
use Spiral\Http\Exception\ClientException\ForbiddenException;
use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Http\Exception\ClientException\UnauthorizedException;

/**
 * The class is responsible for:
 *   - Global error handling (outside of dispatchers) using the {@see handleGlobalException()} method.
 *     Use the {@see register()} method to register the handler as a global exception/error catcher.
 *   - Runtime error handling (in a dispatcher after booting) using reporters and renderers.
 *     Use the {@see render()} method to prepare a formatted exception output.
 *     Use the {@see report()} method to send a debug information to configured channels.
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    public ?Verbosity $verbosity = Verbosity::BASIC;

    /** @var array<int, ExceptionRendererInterface> */
    protected array $renderers = [];
    /** @var array<int, ExceptionReporterInterface|Closure> */
    protected array $reporters = [];
    protected mixed $output = null;
    protected array $nonReportableExceptions = [
        BadRequestException::class,
        ForbiddenException::class,
        NotFoundException::class,
        UnauthorizedException::class,
        AuthorizationException::class,
        ValidationException::class,
    ];

    public function __construct()
    {
        $this->bootBasicHandlers();
    }

    public function register(): void
    {
        \register_shutdown_function($this->handleShutdown(...));
        \set_error_handler($this->handleError(...));
        \set_exception_handler($this->handleGlobalException(...));
    }

    public function getRenderer(?string $format = null): ?ExceptionRendererInterface
    {
        if ($format !== null) {
            foreach ($this->renderers as $renderer) {
                if ($renderer->canRender($format)) {
                    return $renderer;
                }
            }
        }
        return \end($this->renderers) ?: null;
    }

    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = null,
        string $format = null,
    ): string {
        return (string)$this->getRenderer($format)?->render($exception, $verbosity ?? $this->verbosity, $format);
    }

    public function canRender(string $format): bool
    {
        return $this->getRenderer($format) !== null;
    }

    public function report(\Throwable $exception): void
    {
        if ($this->shouldNotReport($exception)) {
            return;
        }

        foreach ($this->reporters as $reporter) {
            try {
                if ($reporter instanceof ExceptionReporterInterface) {
                    $reporter->report($exception);
                } else {
                    $reporter($exception);
                }
            } catch (\Throwable) {
                // Do nothing
            }
        }
    }

    public function handleGlobalException(\Throwable $e): void
    {
        if (\in_array(PHP_SAPI, ['cli', 'cli-server'], true)) {
            $this->output ??= \defined('STDERR') ? \STDERR : \fopen('php://stderr', 'wb+');
            $format = 'cli';
        } else {
            $this->output ??= \defined('STDOUT') ? \STDOUT : \fopen('php://stdout', 'wb+');
            $format = 'html';
        }

        // we are safe to handle global exceptions (system level) with maximum verbosity
        $this->report($e);

        // There is possible an exception on the application termination
        try {
            \fwrite($this->output, $this->render($e, verbosity: $this->verbosity, format: $format));
        } catch (\Throwable) {
            $this->output = null;
        }
    }

    /**
     * Add renderer to the beginning of the renderers list
     */
    public function addRenderer(ExceptionRendererInterface $renderer): void
    {
        \array_unshift($this->renderers, $renderer);
    }

    /**
     * @param class-string<\Throwable> $exception
     */
    public function dontReport(string $exception): void
    {
        $this->nonReportableExceptions[] = $exception;
    }

    /**
     * @param ExceptionReporterInterface|Closure(\Throwable):void $reporter
     */
    public function addReporter(ExceptionReporterInterface|Closure $reporter): void
    {
        $this->reporters[] = $reporter;
    }

    /**
     * @param resource $output
     */
    public function setOutput(mixed $output): void
    {
        $this->output = $output;
    }

    /**
     * Handle php shutdown and search for fatal errors.
     */
    protected function handleShutdown(): void
    {
        if (empty($error = \error_get_last())) {
            return;
        }

        try {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        } catch (\Throwable $e) {
            $this->handleGlobalException($e);
        }
    }

    /**
     * Convert application error into exception.
     * Handler for the {@see \set_error_handler()}.
     * @throws \ErrorException
     */
    protected function handleError(
        int $errno,
        string $errstr,
        string $errfile = '',
        int $errline = 0,
    ): bool {
        if (!(\error_reporting() & $errno)) {
            return false;
        }

        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    protected function bootBasicHandlers(): void
    {
        $this->addRenderer(new PlainRenderer());
    }

    protected function shouldNotReport(\Throwable $exception): bool
    {
        foreach ($this->nonReportableExceptions as $nonReportableException) {
            if ($exception instanceof $nonReportableException) {
                return true;
            }
        }

        $attribute = (new \ReflectionClass($exception))->getAttributes(NonReportable::class)[0] ?? null;

        return $attribute !== null;
    }
}
