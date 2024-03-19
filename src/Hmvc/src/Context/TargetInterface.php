<?php

declare(strict_types=1);

namespace Spiral\Core\Context;

use Stringable;

/**
 * The target may be a concrete reflection or an alias.
 * In both cases, you can get a path to the target.
 */
interface TargetInterface extends Stringable
{
    /**
     * @return list<non-empty-string>
     */
    public function getPath(): array;

    public function withPath(array $path): static;

    public function getReflection(): ?\ReflectionFunctionAbstract;

    /**
     * @param \ReflectionFunctionAbstract|null $reflection Pass null to remove the reflection.
     */
    public function withReflection(?\ReflectionFunctionAbstract $reflection): static;
}
