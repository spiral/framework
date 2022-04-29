<?php

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Views\Exception\ContextException;

interface ContextInterface
{
    /**
     * Calculated context id based on values of all dependencies.
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
     * @throws ContextException
     */
    public function withDependency(DependencyInterface $dependency): ContextInterface;

    /**
     * Get calculated dependency value.
     *
     * @throws ContextException
     */
    public function resolveValue(string $dependency): mixed;
}
