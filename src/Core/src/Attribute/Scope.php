<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

/**
 * Set the scope in which the dependency can be resolved.
 *
 * @internal We are testing this feature, it may be changed in the future.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Scope implements Plugin
{
    public readonly string $name;

    public function __construct(string|\BackedEnum $name)
    {
        $this->name = \is_object($name) ? (string) $name->value : $name;
    }
}
