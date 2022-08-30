<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype;

use Spiral\Prototype\ClassNode\Type;

final class Dependency
{
    /** @var Type */
    public $type;

    /** @var string */
    public $property;

    /** @var string */
    public $var;

    /**
     * Dependency constructor.
     */
    private function __construct()
    {
    }

    public static function create(string $name, string $type): Dependency
    {
        $dependency = new self();
        $dependency->type = Type::create($type);
        $dependency->property = $name;
        $dependency->var = $name;

        return $dependency;
    }
}
