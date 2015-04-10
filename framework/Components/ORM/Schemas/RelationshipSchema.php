<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas;

use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\SchemaReader;
use Spiral\Core\Container;

abstract class RelationshipSchema
{
    /**
     * Relationship type.
     */
    const RELATIONSHIP_TYPE = null;

    /**
     * Equivalent relationship resolved based on definition and not schema, usually polymorphic.
     */
    const EQUIVALENT_RELATIONSHIP = null;

    /**
     * Parent ORM schema holds all entity schemas.
     *
     * @invisible
     * @var SchemaReader
     */
    protected $ormSchema = null;

    /**
     * Relationship name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Relationship definition.
     *
     * @var array
     */
    protected $definition = array();

    /**
     * Target model or interface (for polymorphic classes).
     *
     * @var string
     */
    protected $target = '';

    public function __construct(SchemaReader $ormSchema, $name, array $definition)
    {
        $this->ormSchema = $ormSchema;
        $this->name = $name;
        $this->definition = $definition;

        $this->target = $definition[static::RELATIONSHIP_TYPE];
    }

    /**
     * Relationship type.
     *
     * @return int
     */
    public function getType()
    {
        return static::RELATIONSHIP_TYPE;
    }

    /**
     * Check if relationship has equivalent based on declared definition, default behaviour will
     * select polymorphic equivalent if target declared as interface.
     *
     * @return bool
     */
    public function hasEquivalent()
    {
        if (!static::EQUIVALENT_RELATIONSHIP)
        {
            return false;
        }

        $reflection = new \ReflectionClass($this->target);

        return $reflection->isInterface();
    }

    /**
     * Get definition for equivalent (usually polymorphic relationship).
     *
     * @return array
     * @throws ORMException
     */
    public function getEquivalentDefinition()
    {
        $definition = $this->definition;
        unset($definition[static::RELATIONSHIP_TYPE]);

        return array(static::EQUIVALENT_RELATIONSHIP => $this->target) + $definition;
    }

    protected function getTargetEntity()
    {
        return $this->ormSchema->getEntity($this->target);
    }

    abstract public function cast(EntitySchema $schema);
}