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

/**
 * Declares set of dependencies for view environment.
 *
 * Attention, dependency set is stated as immutable, THOUGHT calculated values DO depend on
 * container and might change in application lifetime.
 */
final class ViewContext implements ContextInterface
{
    /** @var DependencyInterface[] */
    private $dependencies = [];

    /**
     * {@inheritdoc}
     */
    public function getID(): string
    {
        $calculated = '';
        foreach ($this->dependencies as $dependency) {
            $calculated .= "[{$dependency->getName()}={$dependency->getValue()}]";
        }

        return md5($calculated);
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return array_values($this->dependencies);
    }

    /**
     * {@inheritdoc}
     */
    public function withDependency(DependencyInterface $dependency): ContextInterface
    {
        $environment = clone $this;
        $environment->dependencies[$dependency->getName()] = $dependency;

        return $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveValue(string $dependency)
    {
        if (!isset($this->dependencies[$dependency])) {
            throw new ContextException("Undefined context dependency '{$dependency}'.");
        }

        return $this->dependencies[$dependency]->getValue();
    }
}
