<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas;

use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\SchemaReader;
use Spiral\Core\Container;

abstract class RelationSchema
{
    /**
     * Relation type.
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
     * Associated entity schema.
     *
     * @invisible
     * @var EntitySchema
     */
    protected $entitySchema = null;

    /**
     * Relation name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Relation definition.
     *
     * @var array
     */
    protected $definition = array();

    protected $defaultDefinition = array();

    protected function define($key, $format)
    {
        if (isset($this->definition[$key]))
        {
            return $this->definition[$key];
        }

        return $this->definition[$key] = interpolate($format, array(
            'name'             => $this->name,
            'entity:roleName'  => $this->entitySchema->getRoleName(),
            'entity:table'     => $this->entitySchema->getTable(),
            'entity:pK'        => $this->entitySchema->getPrimaryKey(),
            'foreign:roleName' => $this->getTargetEntity()->getRoleName(),
            'foreign:table'    => $this->getTargetEntity()->getTable(),
            'foreign:pK'       => $this->getTargetEntity()->getPrimaryKey()
        ));
    }


    /**
     * Target model or interface (for polymorphic classes).
     *
     * @var string
     */
    protected $target = '';

    public function __construct(SchemaReader $ormSchema, EntitySchema $entitySchema, $name, array $definition)
    {
        $this->ormSchema = $ormSchema;
        $this->entitySchema = $entitySchema;

        $this->name = $name;
        $this->definition = $definition;
        $this->target = $definition[static::RELATIONSHIP_TYPE];

        if (!$this->hasEquivalent())
        {
            $this->definition = $this->clarifyDefinition($this->definition);
        }
    }

    protected function getDefinitionOptions()
    {
        $keys = array(
            Entity::FOREIGN_KEY => 'FOREIGN_KEY',
            Entity::LOCAL_KEY   => 'LOCAL_KEY',
            Entity::LOCAL_TYPE  => 'LOCAL_TYPE',
            Entity::THOUGHT     => 'THOUGHT',
            Entity::PIVOT_TABLE => 'PIVOT_TABLE',

        );

        $rrr = array();

        foreach ($keys as $key => $name)
        {
            if (isset($this->definition[$key]))
            {
                $rrr[$name] = $this->definition[$key];
            }
        }

        return $rrr + array(
            'name'             => $this->name,
            'entity:roleName'  => $this->entitySchema->getRoleName(),
            'entity:table'     => $this->entitySchema->getTable(),
            'entity:pK'        => $this->entitySchema->getPrimaryKey(),
            'foreign:roleName' => $this->getTargetEntity()->getRoleName(),
            'foreign:table'    => $this->getTargetEntity()->getTable(),
            'foreign:pK'       => $this->getTargetEntity()->getPrimaryKey()
        );
    }


    protected function clarifyDefinition($definition)
    {
        return $definition;
    }

    /**
     * Relation type.
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

    abstract public function initiate();
}