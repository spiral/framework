<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration\Database;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;
use Spiral\Reactor\Partial\Property;

abstract class AbstractEntityDeclaration extends ClassDeclaration implements DependedInterface
{
    /** @var string|null */
    protected $role;

    /** @var string|null */
    protected $mapper;

    /** @var string|null */
    protected $repository;

    /** @var string|null */
    protected $table;

    /** @var string|null */
    protected $database;

    /** @var string|null */
    protected $inflection;

    /**
     * @param string|null $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @param string|null $mapper
     */
    public function setMapper(string $mapper): void
    {
        $this->mapper = $mapper;
    }

    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @param string|null $table
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    public function setInflection(string $inflection): void
    {
        $this->inflection = $inflection;
    }

    /**
     * Add field.
     */
    public function addField(string $name, string $accessibility, string $type): Property
    {
        $property = $this->property($name);
        $property->setComment("@var {$this->variableType($type)}");
        if ($accessibility) {
            $property->setAccess($accessibility);
        }

        if ($property->getAccess() !== self::ACCESS_PUBLIC) {
            $this->declareAccessors($name, $type);
        }

        return $property;
    }

    abstract public function declareSchema(): void;

    protected function isNullableType(string $type): bool
    {
        return strpos($type, '?') === 0;
    }

    private function variableType(string $type): string
    {
        return $this->isNullableType($type) ? (substr($type, 1) . '|null') : $type;
    }

    private function declareAccessors(string $field, string $type): void
    {
        $setter = $this->method('set' . $this->classify($field));
        $setter->setPublic();
        $setter->parameter('value')->setType($type);
        $setter->setSource("\$this->$field = \$value;");

        $getter = $this->method('get' . $this->classify($field));
        $getter->setPublic();
        $getter->setSource("return \$this->$field;");
    }

    private function classify(string $name): string
    {
        return ( new InflectorFactory() )
            ->build()
            ->classify($name);
    }
}
