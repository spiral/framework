<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Views\Exception\ContextException;

interface ContextInterface
{
    /**
     * Calculated context id based on values of all dependencies.
     *
     * @return string
     */
    public function getID(): string;

    /**
     * Get all associated dependencies.
     *
     * @return DependencyInterface[]
     */
    public function getDependencies(): array;

    /**
     * Create environment with new variable dependency.
     *
     * @param DependencyInterface $dependency
     * @return ContextInterface
     *
     * @throws ContextException
     */
    public function withDependency(DependencyInterface $dependency): ContextInterface;

    /**
     * Get calculated dependency value.
     *
     * @param string $dependency
     * @return mixed
     *
     * @throws ContextException
     */
    public function resolveValue(string $dependency);
}
