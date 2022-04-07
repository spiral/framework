<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

class ErrorHandler implements ErrorHandlerInterface
{
    /** @var array<int, ErrorRendererInterface> */
    private array $renderers = [];

    public function addRenderers(ErrorRendererInterface ...$renderers): void
    {
        $this->renderers = \array_merge($this->renderers, $renderers);
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
        return $renderer ?? null;
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

    public function shouldReport(\Throwable $exception): bool
    {
        // todo
        return true;
    }

    public function report(\Throwable $exception, Verbosity $verbosity = null): void
    {
        // echo ' >>"' . $exception->getMessage() . '"<< ';
        echo $this->render($exception, verbosity: Verbosity::VERBOSE);
        // TODO: Implement report() method.
    }
}
