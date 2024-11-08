<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

use Stringable;

/**
 * The target may be a concrete reflection or an alias.
 * In both cases, you can get a path to the target.
 *
 * @template-covariant TController of object|null
 */
interface TargetInterface extends Stringable
{
    /**
     * Returns the path to the target.
     * If the target was created from a method reflection, the path will contain
     * the class name and the method name by default.
     *
     * @return list<string>|list{class-string<TController>, non-empty-string}
     */
    public function getPath(): array;

    /**
     * @param list<string> $path
     * @param string|null $delimiter The delimiter to use when converting the path to a string.
     */
    public function withPath(array $path, ?string $delimiter = null): static;

    /**
     * Returns the reflection of the target.
     *
     * It may be {@see \ReflectionFunction} or {@see \ReflectionMethod}.
     *
     * NOTE:
     * The method {@see \ReflectionMethod::getDeclaringClass()} may return a parent class,
     * but not the class used when the target was created.
     * Use {@see getObject()} or {@see Target::getPath()}[0] to get the original object or class name.
     *
     * @psalm-pure
     */
    public function getReflection(): ?\ReflectionFunctionAbstract;

    /**
     * Returns the object associated with the target.
     *
     * If the object is present, it always corresponds to the method reflection from {@see getReflection()}.
     *
     * @return TController|null
     */
    public function getObject(): ?object;

    /**
     * Returns the callable associated with the target.
     * It may be a classic PHP callable or an array with a class name and a method name.
     *
     * @return callable|array{class-string, non-empty-string}|null
     */
    public function getCallable(): callable|array|null;
}
