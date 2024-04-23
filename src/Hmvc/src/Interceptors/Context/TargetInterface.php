<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

use Stringable;

/**
 * The target may be a concrete reflection or an alias.
 * In both cases, you can get a path to the target.
 */
interface TargetInterface extends Stringable
{
    /**
     * @return list<string>
     */
    public function getPath(): array;

    /**
     * @param list<string> $path
     */
    public function withPath(array $path): static;

    /**
     * @psalm-pure
     */
    public function getReflection(): ?\ReflectionFunctionAbstract;

    /**
     * @param \ReflectionFunctionAbstract|null $reflection Pass null to remove the reflection.
     */
    public function withReflection(?\ReflectionFunctionAbstract $reflection): static;
}
