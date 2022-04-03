<?php

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
    private array $dependencies = [];

    public function getID(): string
    {
        $calculated = '';
        foreach ($this->dependencies as $dependency) {
            $calculated .= \sprintf('[%s=%s]', $dependency->getName(), $dependency->getValue());
        }

        return \md5($calculated);
    }

    /**
     * @return DependencyInterface[]
     *
     * @psalm-return list<DependencyInterface>
     */
    public function getDependencies(): array
    {
        return \array_values($this->dependencies);
    }

    public function withDependency(DependencyInterface $dependency): ContextInterface
    {
        $environment = clone $this;
        $environment->dependencies[$dependency->getName()] = $dependency;

        return $environment;
    }

    public function resolveValue(string $dependency): mixed
    {
        if (!isset($this->dependencies[$dependency])) {
            throw new ContextException(\sprintf('Undefined context dependency \'%s\'.', $dependency));
        }

        return $this->dependencies[$dependency]->getValue();
    }
}
