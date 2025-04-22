<?php

declare(strict_types=1);

namespace Spiral\Prototype;

use Spiral\Prototype\ClassNode\Type;

final class Dependency
{
    public Type $type;
    public string $property;
    public string $var;

    private function __construct() {}

    /**
     * @param non-empty-string $name
     * @param non-empty-string $type
     */
    public static function create(string $name, string $type): Dependency
    {
        $dependency = new self();
        $dependency->type = Type::create($type);
        $dependency->property = $name;
        $dependency->var = $name;

        return $dependency;
    }
}
