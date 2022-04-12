<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

use Closure;

class ErrorHandler implements ErrorHandlerInterface
{
    public ?Verbosity $verbosity = Verbosity::BASIC;

    /** @var array<int, ErrorRendererInterface> */
    private array $renderers = [];
    /** @var array<int, ErrorReporterInterface|Closure> */
    private array $reporters = [];

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
    public function addReporters(ErrorReporterInterface|Closure $reporter): void
    {
        $this->reporters[] = $reporter;
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
        return $this->renderers[\array_key_last($this->renderers)];
    }

    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = null,
        string $format = null,
    ): string {
        return (string) $this->getRenderer($format)?->render($exception, $verbosity ?? $this->verbosity, $format);
    }

    public function canRender(string $format): bool
    {
        return $this->getRenderer($format) !== null;
    }

    public function report(\Throwable $exception): void
    {
        foreach ($this->reporters as $reporter) {
            if ($reporter instanceof ErrorReporterInterface) {
                $reporter->report($exception);
            } else {
                $reporter($exception);
            }
        }
    }
}
