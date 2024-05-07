<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

/**
 * @template TController
 */
final class Target implements TargetInterface
{
    /**
     * @param list<string> $path
     * @param \ReflectionFunctionAbstract|null $reflection
     */
    private function __construct(
        private ?array $path = null,
        private ?\ReflectionFunctionAbstract $reflection = null,
        private ?object $object = null,
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

    /**
     * Create a target from a method reflection.
     *
     * @template T of object
     *
     * @param \ReflectionMethod $reflection
     * @param class-string<T>|T $classOrObject The original class name or object.
     *        It's required because the reflection may be referring to a parent class method.
     *        THe path will contain the original class name and the method name.
     *
     * @return self<T>
     */
    public static function fromReflectionMethod(
        \ReflectionFunctionAbstract $reflection,
        string|object $classOrObject,
    ): self {
        return \is_object($classOrObject)
            ? new self(
                path: [$classOrObject::class, $reflection->getName()],
                reflection: $reflection,
                object: $classOrObject,
            )
            : new self(
                path: [$classOrObject, $reflection->getName()],
                reflection: $reflection,
            );
    }

    /**
     * Create a target from a function reflection.
     *
     * @return self<null>
     */
    public static function fromReflectionFunction(\ReflectionFunction $reflection): self
    {
        return new self(reflection: $reflection);
    }

    /**
     * Create a target from a path string without reflection.
     *
     * @return self<null>
     */
    public static function fromPathString(string $path, string $delimiter = '.'): self
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new self(path: \explode($delimiter, $path), delimiter: $delimiter);
    }

    /**
     * Create a target from a path array without reflection.
     *
     * @param list<string> $path
     * @return self<null>
     */
    public static function fromPathArray(array $path, string $delimiter = '.'): self
    {
        return new self(path: $path, delimiter: $delimiter);
    }

    /**
     * Create a target from a controller and action pair.
     * If the action is a method of the controller, the reflection will be set.
     *
     * @template T
     *
     * @param non-empty-string|class-string<T> $controller
     * @param non-empty-string $action
     *
     * @return ($controller is class-string ? self<T> : self<null>)
     */
    public static function fromPair(string $controller, string $action): self
    {
        $target = \method_exists($controller, $action)
            ? self::fromReflectionMethod(new \ReflectionMethod($controller, $action), $controller)
            : self::fromPathArray([$controller, $action]);
        return $target->withPath([$controller, $action]);
    }

    /**
     * @return list<string>|list{class-string<TController>, non-empty-string}
     */
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

    /**
     * @return TController|null
     */
    public function getObject(): ?object
    {
        return $this->object;
    }
}
