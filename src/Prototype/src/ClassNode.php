<?php

declare(strict_types=1);

namespace Spiral\Prototype;

use Spiral\Prototype\ClassNode\ConstructorParam;

/**
 * @internal
 */
final class ClassNode
{
    public string $namespace;
    public string $class;
    public bool $hasConstructor = false;

    /** @var ClassNode\ConstructorParam[] */
    public array $constructorParams = [];

    /** @var string[] */
    public array $constructorVars = [];

    /** @var Dependency[] */
    public array $dependencies = [];

    /** @var ClassNode\ClassStmt[] */
    private array $stmts = [];

    private function __construct()
    {
    }

    public static function create(string $class): ClassNode
    {
        $self = new self();
        $self->class = $class;

        return $self;
    }

    public static function createWithNamespace(string $class, string $namespace): ClassNode
    {
        $self = new self();
        $self->class = $class;
        $self->namespace = $namespace;

        return $self;
    }

    public function addImportUsage(string $name, ?string $alias): void
    {
        $this->addStmt(ClassNode\ClassStmt::create($name, $alias));
    }

    /**
     * @return ClassNode\ClassStmt[]
     */
    public function getStmts(): array
    {
        return $this->stmts;
    }

    /**
     * @throws \ReflectionException
     */
    public function addParam(\ReflectionParameter $parameter): void
    {
        $this->constructorParams[$parameter->name] = ConstructorParam::createFromReflection($parameter);
    }

    private function addStmt(ClassNode\ClassStmt $stmt): void
    {
        $this->stmts[(string)$stmt] = $stmt;
    }
}
