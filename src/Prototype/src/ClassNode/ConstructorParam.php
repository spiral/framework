<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode;

final class ConstructorParam
{
    public string $name;
    public bool $nullable = false;
    public bool $hasDefault = false;
    public mixed $default = null;
    public ?Type $type = null;
    public bool $byRef = false;
    public bool $isVariadic = false;
    private bool $builtIn = false;

    private function __construct()
    {
    }

    /**
     *
     * @throws \ReflectionException
     */
    public static function createFromReflection(\ReflectionParameter $parameter): ConstructorParam
    {
        $stmt = new self();
        $stmt->name = $parameter->getName();

        $type = $parameter->getType();
        if ($type instanceof \ReflectionNamedType) {
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
