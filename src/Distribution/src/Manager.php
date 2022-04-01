<?php

declare(strict_types=1);

namespace Spiral\Distribution;

final class Manager implements MutableDistributionInterface
{
    public const DEFAULT_RESOLVER = 'default';
    private const ERROR_REDEFINITION = 'Can not redefine already defined distribution resolver `%s`';
    private const ERROR_NOT_FOUND = 'Distribution resolver `%s` has not been defined';

    /**
     * @var array<string, UriResolverInterface>
     */
    private array $resolvers = [];
    private string $default;

    public function __construct(string $name = self::DEFAULT_RESOLVER)
    {
        $this->default = $name;
    }

    /**
     * @return $this
     */
    public function withDefault(string $name): DistributionInterface
    {
        $self = clone $this;
        $self->default = $name;

        return $self;
    }

    public function resolver(string $name = null): UriResolverInterface
    {
        $name ??= $this->default;

        if (!isset($this->resolvers[$name])) {
            throw new \InvalidArgumentException(\sprintf(self::ERROR_NOT_FOUND, $name));
        }

        return $this->resolvers[$name];
    }

    public function add(string $name, UriResolverInterface $resolver, bool $overwrite = false): void
    {
        if ($overwrite === false && isset($this->resolvers[$name])) {
            throw new \InvalidArgumentException(\sprintf(self::ERROR_REDEFINITION, $name));
        }

        $this->resolvers[$name] = $resolver;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->resolvers);
    }

    public function count(): int
    {
        return \count($this->resolvers);
    }
}
