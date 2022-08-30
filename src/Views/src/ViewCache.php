<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Views\Exception\CacheException;

final class ViewCache
{
    /** @var array */
    private $cache = [];

    /**
     * @param ContextInterface|null $context
     */
    public function reset(ContextInterface $context = null): void
    {
        if (empty($context)) {
            $this->cache = [];
            return;
        }

        unset($this->cache[$context->getID()]);
    }

    /**
     * Reset view cache from all the contexts.
     */
    public function resetPath(string $path): void
    {
        foreach ($this->cache as &$cache) {
            unset($cache[$path], $cache);
        }
    }

    public function has(ContextInterface $context, string $path): bool
    {
        return isset($this->cache[$context->getID()][$path]);
    }

    public function set(ContextInterface $context, string $path, ViewInterface $view): void
    {
        $this->cache[$context->getID()][$path] = $view;
    }

    /**
     *
     * @throws CacheException
     */
    public function get(ContextInterface $context, string $path): ViewInterface
    {
        if (!$this->has($context, $path)) {
            throw new CacheException("No cache is available for {$path}.");
        }

        return $this->cache[$context->getID()][$path];
    }
}
