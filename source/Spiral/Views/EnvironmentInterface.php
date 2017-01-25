<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views;

use Spiral\Views\Exceptions\EnvironmentException;

/**
 * View environment is class responsible to view isolation based on some external value such as
 * language, base path and.
 */
interface EnvironmentInterface
{
    /**
     * Check if caching is enabled.
     *
     * @return bool
     */
    public function isCachable(): bool;

    /**
     * @return string
     */
    public function cacheDirectory(): string;

    /**
     * Create environment with new binded dependency. Must not affect existed environment
     * dependencies.
     *
     * @param string   $dependency
     * @param callable $source
     *
     * @return self
     *
     * @throws EnvironmentException
     */
    public function withDependency(string $dependency, callable $source): self;

    /**
     * Get calculated dependency value.
     *
     * @param string $dependency
     *
     * @return mixed
     * @throws EnvironmentException
     */
    public function getValue(string $dependency);

    /**
     * Calculated environment id based on all dependencies.
     *
     * @return string
     */
    public function getID(): string;
}