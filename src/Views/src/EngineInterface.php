<?php

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Views\Exception\EngineException;
use Spiral\Views\Exception\LoaderException;

interface EngineInterface
{
    /**
     * Configure view engine with new loader.
     */
    public function withLoader(LoaderInterface $loader): EngineInterface;

    /**
     * Get currently associated engine loader.
     *
     * @throws EngineException
     */
    public function getLoader(): LoaderInterface;

    /**
     * Compile (and reset cache) for the given view path in a provided context. This method must be
     * called each time view must be re-compiled.
     *
     * @throws EngineException
     * @throws LoaderException
     */
    public function compile(string $path, ContextInterface $context): mixed;

    /**
     * Reset view cache.
     */
    public function reset(string $path, ContextInterface $context): void;

    /**
     * Get instance of view class associated with view path (path can include namespace). Engine
     * must attempt to use existed cache if such presented (or compile view directly if cache has
     * been disabled).
     *
     * @throws EngineException
     * @throws LoaderException
     */
    public function get(string $path, ContextInterface $context): ViewInterface;
}
