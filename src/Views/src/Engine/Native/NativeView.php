<?php

declare(strict_types=1);

namespace Spiral\Views\Engine\Native;

use Psr\Container\ContainerInterface;
use Spiral\Core\ContainerScope;
use Spiral\Views\Exception\RenderException;
use Spiral\Views\ViewInterface;
use Spiral\Views\ViewSource;

final class NativeView implements ViewInterface
{
    public function __construct(
        private readonly ViewSource $view,
        private readonly ContainerInterface $container
    ) {
    }

    public function render(array $data = []): string
    {
        \ob_start();
        $__outputLevel__ = \ob_get_level();

        try {
            ContainerScope::runScope($this->container, function () use ($data): void {
                \extract($data, EXTR_OVERWRITE);
                // render view in context and output buffer scope, context can be accessed using $this->context
                require $this->view->getFilename();
            });
        } catch (\Throwable $throwable) {
            while (\ob_get_level() >= $__outputLevel__) {
                \ob_end_clean();
            }

            throw new RenderException($throwable);
        } finally {
            //Closing all nested buffers
            while (\ob_get_level() > $__outputLevel__) {
                \ob_end_clean();
            }
        }

        return \ob_get_clean();
    }
}
