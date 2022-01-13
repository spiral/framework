<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Core\FactoryInterface;
use Spiral\Views\Config\ViewsConfig;
use Spiral\Views\Exception\ViewException;

final class ViewManager implements ViewsInterface
{
    /** @var ViewsConfig */
    private $config;

    /** @var ViewContext */
    private $context;

    /** @var LoaderInterface */
    private $loader;

    /** @var ViewCache|null */
    private $cache;

    /** @var EngineInterface[] */
    private $engines;

    public function __construct(ViewsConfig $config, FactoryInterface $factory)
    {
        $this->config = $config;
        $this->context = new ViewContext();
        $this->loader = $factory->make(ViewLoader::class, [
            'namespaces' => $config->getNamespaces(),
        ]);

        foreach ($this->config->getDependencies() as $dependency) {
            $this->addDependency($dependency->resolve($factory));
        }

        foreach ($this->config->getEngines() as $engine) {
            $this->addEngine($engine->resolve($factory));
        }

        if ($this->config->isCacheEnabled()) {
            $this->cache = new ViewCache();
        }
    }

    /**
     * Attach new view context dependency.
     */
    public function addDependency(DependencyInterface $dependency): void
    {
        $this->context = $this->context->withDependency($dependency);
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * Attach new view engine.
     */
    public function addEngine(EngineInterface $engine): void
    {
        $this->engines[] = $engine->withLoader($this->loader);

        uasort($this->engines, function (EngineInterface $a, EngineInterface $b) {
            return strcmp($a->getLoader()->getExtension(), $b->getLoader()->getExtension());
        });

        $this->engines = array_values($this->engines);
    }

    /**
     * Get all associated view engines.
     *
     * @return EngineInterface[]
     */
    public function getEngines(): array
    {
        return $this->engines;
    }

    /**
     * Compile one of multiple cache versions for a given view path.
     *
     *
     * @throws ViewException
     */
    public function compile(string $path): void
    {
        if ($this->cache !== null) {
            $this->cache->resetPath($path);
        }

        $engine = $this->findEngine($path);

        // Rotate all possible context variants and warm up cache
        $generator = new ContextGenerator($this->context);
        foreach ($generator->generate() as $context) {
            $engine->reset($path, $context);
            $engine->compile($path, $context);
        }
    }

    /**
     * Reset view cache for a given path. Identical to compile method by effect but faster.
     */
    public function reset(string $path): void
    {
        if ($this->cache !== null) {
            $this->cache->resetPath($path);
        }

        $engine = $this->findEngine($path);

        // Rotate all possible context variants and warm up cache
        $generator = new ContextGenerator($this->context);
        foreach ($generator->generate() as $context) {
            $engine->reset($path, $context);
        }
    }

    /**
     * Get view from one of the associated engines.
     *
     *
     * @throws ViewException
     */
    public function get(string $path): ViewInterface
    {
        if ($this->cache !== null && $this->cache->has($this->context, $path)) {
            return $this->cache->get($this->context, $path);
        }

        $view = $this->findEngine($path)->get($path, $this->context);

        if ($this->cache !== null) {
            $this->cache->set($this->context, $path, $view);
        }

        return $view;
    }

    /**
     *
     * @throws ViewException
     */
    public function render(string $path, array $data = []): string
    {
        return $this->get($path)->render($data);
    }

    /**
     *
     * @throws ViewException
     */
    protected function findEngine(string $path): EngineInterface
    {
        foreach ($this->engines as $engine) {
            if ($engine->getLoader()->exists($path)) {
                return $engine;
            }
        }

        throw new ViewException("Unable to detect view engine for `{$path}`.");
    }
}
