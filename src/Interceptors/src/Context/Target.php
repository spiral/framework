<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

/**
 * @template-covariant TController of object|null
 * @implements TargetInterface<TController>
 */
final class Target implements TargetInterface
{
    /**
     * @param list<string> $path
     * @param TController|null $object
     * @param \Closure|array{class-string|object, non-empty-string}|null $callable
     */
    private function __construct(
        private array $path,
        private ?\ReflectionFunctionAbstract $reflection = null,
        private readonly ?object $object = null,
        private string $delimiter = '.',
        private readonly \Closure|array|null $callable = null,
    ) {
    }

    public function __toString(): string
    {
        return \implode($this->delimiter, $this->path);
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
     * @psalmif
     *
     * @return self<T>
     */
    public static function fromReflectionMethod(
        \ReflectionFunctionAbstract $reflection,
        string|object $classOrObject,
    ): self {
        $method = $reflection->getName();
        $isStatic = $reflection->isStatic();

        /** @var self<T> $result */
        $result = \is_object($classOrObject)
            ? new self(
                path: [$classOrObject::class, $method],
                reflection: $reflection,
                object: $classOrObject,
                delimiter: $isStatic ? '::' : '->',
                callable: [$classOrObject, $method],
            )
            : new self(
                path: [$classOrObject, $method],
                reflection: $reflection,
                delimiter: $isStatic ? '::' : '->',
                callable: [$classOrObject, $method],
            );
        return $result;
    }

    /**
     * Create a target from a function reflection.
     *
     * @param list<string> $path
     *
     * @return self<null>
     */
    public static function fromReflectionFunction(\ReflectionFunction $reflection, array $path = []): self
    {
        /** @var self<null> $result */
        $result = new self(path: $path, reflection: $reflection, callable: $reflection->getClosure());
        return $result;
    }

    /**
     * Create a target from a closure.
     *
     * @param list<string> $path
     *
     * @return self<null>
     */
    public static function fromClosure(\Closure $closure, array $path = []): self
    {
        /** @var self<null> $result */
        $result = new self(path: $path, reflection: new \ReflectionFunction($closure), callable: $closure);
        return $result;
    }

    /**
     * Create a target from a path string without reflection.
     *
     * @param non-empty-string $delimiter
     *
     * @return self<null>
     */
    public static function fromPathString(string $path, string $delimiter = '.'): self
    {
        return self::fromPathArray(\explode($delimiter, $path), $delimiter);
    }

    /**
     * Create a target from a path array without reflection.
     *
     * @param list<string> $path
     * @return self<null>
     */
    public static function fromPathArray(array $path, string $delimiter = '.'): self
    {
        /** @var self<null> $result */
        $result = new self(path: $path, delimiter: $delimiter);

        return $result;
    }

    /**
     * Create a target from a controller and action pair.
     * If the action is a method of the controller, the reflection will be set.
     *
     * @template T of object
     *
     * @param non-empty-string|class-string<T>|T $controller
     * @param non-empty-string $action
     *
     * @return ($controller is class-string|T ? self<T> : self<null>)
     */
    public static function fromPair(string|object $controller, string $action): self
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        if (\is_object($controller) || \method_exists($controller, $action)) {
            /** @var T|class-string<T> $controller */
            return self::fromReflectionMethod(new \ReflectionMethod($controller, $action), $controller);
        }

        return self::fromPathArray([$controller, $action]);
    }

    public function getPath(): array
    {
        return $this->path;
    }

    public function withPath(array $path, ?string $delimiter = null): static
    {
        $clone = clone $this;
        $clone->path = $path;
        $clone->delimiter = $delimiter ?? $clone->delimiter;
        return $clone;
    }

    public function getReflection(): ?\ReflectionFunctionAbstract
    {
        return $this->reflection;
    }

    public function getObject(): ?object
    {
        return $this->object;
    }

    public function getCallable(): callable|array|null
    {
        return $this->callable;
    }
}
