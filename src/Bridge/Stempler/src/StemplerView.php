<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    /** @var StemplerEngine */
    protected $engine;

    /** @var ViewSource */
    protected $view;

    /** @var ContextInterface */
    protected $context;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param StemplerEngine   $engine
     * @param ViewSource       $view
     * @param ContextInterface $context
     */
    public function __construct(StemplerEngine $engine, ViewSource $view, ContextInterface $context)
    {
        $this->engine = $engine;
        $this->view = $view;
        $this->context = $context;
        $this->container = $engine->getContainer();
    }

    /**
     * @param int        $lineOffset
     * @param \Throwable $e
     * @param array      $data
     * @return \Throwable
     */
    protected function mapException(int $lineOffset, \Throwable $e, array $data)
    {
        $sourcemap = $this->engine->makeSourceMap(
            sprintf('%s:%s', $this->view->getNamespace(), $this->view->getName()),
            $this->context
        );

        if ($sourcemap === null) {
            return $e;
        }

        $mapper = new ExceptionMapper($sourcemap, $lineOffset);

        return $mapper->mapException($e, static::class, $this->view->getFilename(), $data);
    }
}
