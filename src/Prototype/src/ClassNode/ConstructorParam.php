<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode;

use ReflectionException;
use ReflectionParameter;
use ReflectionNamedType;

final class ConstructorParam
{
    /** @var string */
    public $name;

    /** @var bool */
    public $nullable;

    /** @var bool */
    public $hasDefault;

    /** @var mixed|null */
    public $default;

    /** @var Type|null */
    public $type;

    /** @var bool */
    public $byRef = false;

    /** @var bool */
    public $isVariadic = false;

    private ?bool $builtIn = null;

    /**
     * ConstructorParam constructor.
     */
    private function __construct()
    {
    }

    /**
     *
     * @throws ReflectionException
     */
    public static function createFromReflection(ReflectionParameter $parameter): ConstructorParam
    {
        $stmt = new self();
        $stmt->name = $parameter->getName();

        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType) {
            $stmt->type = Type::create($type->getName());
            $stmt->builtIn = $type->isBuiltin();
            $stmt->nullable = $type->allowsNull();
        }

        if ($parameter->isDefaultValueAvailable()) {
            $stmt->hasDefault = true;
            $stmt->default = $parameter->getDefaultValue();
        }

        $stmt->byRef = $parameter->isPassedByReference();
        $stmt->isVariadic = $parameter->isVariadic();

        return $stmt;
    }

    public function isBuiltIn(): bool
    {
        return $this->builtIn;
    }
}
