<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

use Closure;

class ErrorHandler implements ErrorHandlerInterface
{
    /** @var array<int, ErrorRendererInterface> */
    private array $renderers = [];
    /** @var array<int, ErrorReporterInterface|Closure> */
    private array $reporters = [];

    public function addRenderers(ErrorRendererInterface ...$renderers): void
    {
        $this->renderers = \array_merge($this->renderers, $renderers);
    }

    /**
     * @param ErrorReporterInterface|Closure(\Throwable):void ...$reporters
     */
    public function addReporters(ErrorReporterInterface|Closure ...$reporters): void
    {
        $this->reporters = \array_merge($this->reporters, $reporters);
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
        ?Verbosity $verbosity = Verbosity::BASIC,
        string $format = null,
    ): string {
        return (string) $this->getRenderer($format)?->render($exception, $verbosity, $format);
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
