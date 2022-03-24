<?php

declare(strict_types=1);

namespace Spiral\Stempler;

use Psr\Container\ContainerInterface;
use Spiral\Views\ContextInterface;
use Spiral\Views\ViewInterface;
use Spiral\Views\ViewSource;

/**
 * Stempler views are executed within global container scope.
 */
abstract class StemplerView implements ViewInterface
{
    protected ContainerInterface $container;

    public function __construct(
        protected StemplerEngine $engine,
        protected ViewSource $view,
        protected ContextInterface $context
    ) {
        $this->container = $engine->getContainer();
    }

    /**
     * @return \Throwable
     */
    protected function mapException(int $lineOffset, \Throwable $e, array $data)
    {
        $sourcemap = $this->engine->makeSourceMap(
            \sprintf('%s:%s', $this->view->getNamespace(), $this->view->getName()),
            $this->context
        );

        if ($sourcemap === null) {
            return $e;
        }

        $mapper = new ExceptionMapper($sourcemap, $lineOffset);

        return $mapper->mapException($e, static::class, $this->view->getFilename(), $data);
    }
}
