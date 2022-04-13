<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

use Closure;
use Spiral\Exceptions\Exception\FatalException;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\PlainRenderer;

/**
 * The class is responsible for:
 *   - Global error handling (outside of dispatchers) using the {@see handleGlobalException()} method.
 *     Use the {@see register()} method to register the handler as a global exception/error catcher.
 *   - Runtime error handling (in a dispatcher after booting) using reporters and renderers.
 *     Use the {@see render()} method to prepare a formatted exception output.
 *     Use the {@see report()} method to send a debug information to configured channels.
 */
class ErrorHandler implements ErrorHandlerInterface
{
    public ?Verbosity $verbosity = Verbosity::BASIC;

    /** @var array<int, ErrorRendererInterface> */
    protected array $renderers = [];
    /** @var array<int, ErrorReporterInterface|Closure> */
    protected array $reporters = [];
    protected mixed $output = null;

    public function __construct()
    {
        $this->createBasicHandlers();
    }

    public function register(): void
    {
        \register_shutdown_function($this->handleShutdown(...));
        \set_error_handler($this->handleError(...));
        \set_exception_handler($this->handleGlobalException(...));
    }

    public function getRenderer(?string $format = null): ?ErrorRendererInterface
    {
        if ($format !== null) {
            foreach ($this->renderers as $renderer) {
                if ($renderer->canRender($format)) {
                    return $renderer;
                }
            }
        }
        return $this->renderers[\array_key_last($this->renderers)] ?? null;
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
        foreach ($this->reporters as $reporter) {
            try {
                if ($reporter instanceof ErrorReporterInterface) {
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
        \fwrite($this->output, $this->render($e, verbosity: $this->verbosity, format: $format));
    }

    /**
     * Add renderer to the beginning of the renderers list
     */
    public function addRenderer(ErrorRendererInterface $renderer): void
    {
        \array_unshift($this->renderers, $renderer);
    }

    /**
     * @param ErrorReporterInterface|Closure(\Throwable):void $reporter
     */
    public function addReporter(ErrorReporterInterface|Closure $reporter): void
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
        if (!empty($error = \error_get_last())) {
            $this->handleGlobalException(
                new FatalException(
                    $error['message'],
                    $error['type'],
                    0,
                    $error['file'],
                    $error['line']
                )
            );
        }
    }

    /**
     * Convert application error into exception.
     *
     * @throws \ErrorException
     */
    protected function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): void
    {
        if (!(\error_reporting() & $errno)) {
            return;
        }

        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    protected function createBasicHandlers(): void
    {
        $this->addRenderer(new PlainRenderer());
        $this->addRenderer(new ConsoleRenderer());
    }
}
