<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Distribution;

final class Manager implements MutableManagerInterface
{
    /**
     * @var string
     */
    public const DEFAULT_RESOLVER = 'default';

    /**
     * @var string
     */
    private const ERROR_REDEFINITION = 'Can not redefine already defined distribution resolver `%s`';

    /**
     * @var string
     */
    private const ERROR_NOT_FOUND = 'Distribution resolver `%s` has not been defined';

    /**
     * @var array<string, ResolverInterface>
     */
    private $resolvers = [];

    /**
     * @var string
     */
    private $default;

    /**
     * @param string $name
     */
    public function __construct(string $name = self::DEFAULT_RESOLVER)
    {
        $this->default = $name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function withDefault(string $name): ManagerInterface
    {
        $self = clone $this;
        $self->default = $name;

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function resolver(string $name = null): ResolverInterface
    {
        $name = $name ?? $this->default;

        if (! isset($this->resolvers[$name])) {
            throw new \InvalidArgumentException(\sprintf(self::ERROR_NOT_FOUND, $name));
        }

        return $this->resolvers[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function add(string $name, ResolverInterface $resolver, bool $overwrite = false): void
    {
        if ($overwrite === false && isset($this->resolvers[$name])) {
            throw new \InvalidArgumentException(\sprintf(self::ERROR_REDEFINITION, $name));
        }

        $this->resolvers[$name] = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->resolvers);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->resolvers);
    }
}
