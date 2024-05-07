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
     * Note: {@see \ReflectionMethod::getDeclaringClass()} may return a parent class, but not the class used
     *       when the target was created. Use {@see Target::getPath()} to get the original target class.
     *
     * @psalm-pure
     */
    public function getReflection(): ?\ReflectionFunctionAbstract;

    /**
     * @return TController|null
     */
    public function getObject(): ?object;
}
