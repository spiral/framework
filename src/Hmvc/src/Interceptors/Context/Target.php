<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

final class Target implements TargetInterface
{
    /**
     * @param list<string> $path
     * @param \ReflectionFunctionAbstract|null $reflection
     */
    private function __construct(
        private ?array $path = null,
        private ?\ReflectionFunctionAbstract $reflection = null,
        private readonly string $delimiter = '.',
    ) {
    }

    public function __toString(): string
    {
        return match (true) {
            $this->path !== null => \implode($this->delimiter, $this->path),
            $this->reflection !== null => $this->reflection->getName(),
        };
    }

    public static function fromReflection(\ReflectionFunctionAbstract $reflection): static
    {
        return new self(reflection: $reflection);
    }

    public static function fromPathString(string $path, string $delimiter = '.'): static
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new self(path: \explode($delimiter, $path), delimiter: $delimiter);
    }

    /**
     * @param list<string> $path
     */
    public static function fromPathArray(array $path, string $delimiter = '.'): static
    {
        return new self(path: $path, delimiter: $delimiter);
    }

    public function getPath(): array
    {
        return match (true) {
            $this->path !== null => $this->path,
            $this->reflection instanceof \ReflectionMethod => [
                $this->reflection->getDeclaringClass()->getName(),
                $this->reflection->getName(),
            ],
            $this->reflection instanceof \ReflectionFunction => [$this->reflection->getName()],
            default => [],
        };
    }

    public function withPath(array $path): static
    {
        $clone = clone $this;
        $clone->path = $path;
        return $clone;
    }

    public function getReflection(): ?\ReflectionFunctionAbstract
    {
        return $this->reflection;
    }

    public function withReflection(?\ReflectionFunctionAbstract $reflection): static
    {
        $clone = clone $this;
        $clone->reflection = $reflection;
        return $clone;
    }
}
