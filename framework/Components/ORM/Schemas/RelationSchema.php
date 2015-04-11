<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\SchemaReader;
use Spiral\Core\Container;

abstract class RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = null;

    /**
     * Equivalent relationship resolved based on definition and not schema, usually polymorphic.
     */
    const EQUIVALENT_RELATION = null;

    /**
     * Size of string column dedicated to store outer role name.
     */
    const TYPE_COLUMN_SIZE = 32;

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

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     * Every parameter described by it's key and pattern.
     *
     * Example:
     * Entity::INNER_KEY => '{outer:roleName}_{outer:primaryKey}'
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array();

    /**
     * Target model or interface (for polymorphic classes).
     *
     * @var string
     */
    protected $target = '';

    /**
     * New RelationSchema instance.
     *
     * @param SchemaReader $ormSchema
     * @param EntitySchema $entitySchema
     * @param string       $name
     * @param array        $definition
     */
    public function __construct(
        SchemaReader $ormSchema,
        EntitySchema $entitySchema,
        $name,
        array $definition
    )
    {
        $this->ormSchema = $ormSchema;
        $this->entitySchema = $entitySchema;

        $this->name = $name;
        $this->target = $definition[static::RELATION_TYPE];

        $this->definition = $definition;
        if ($this->hasEquivalent())
        {
            return;
        }

        if (!class_exists($this->target) && !interface_exists($this->target))
        {
            throw new ORMException(
                "Unable to build relation from '{$this->entitySchema}' "
                . "to undefined target '{$this->target}'."
            );
        }

        $this->clarifyDefinition();
    }

    /**
     * Mount default values to relation definition.
     */
    protected function clarifyDefinition()
    {
        foreach ($this->defaultDefinition as $property => $pattern)
        {
            if (isset($this->definition[$property]))
            {
                continue;
            }

            if (!is_string($pattern))
            {
                $this->definition[$property] = $pattern;
                continue;
            }

            $this->definition[$property] = interpolate($pattern, $this->definitionOptions());
        }
    }

    /**
     * Option string used to populate definition template if no user value provided.
     *
     * @return array
     */
    protected function definitionOptions()
    {
        $options = array(
            'name'              => $this->name,
            'name:plural'       => Inflector::pluralize($this->name),
            'name:singular'     => Inflector::singularize($this->name),
            'entity:roleName'   => $this->entitySchema->getRoleName(),
            'entity:table'      => $this->entitySchema->getTable(),
            'entity:primaryKey' => $this->entitySchema->getPrimaryKey(),
        );

        $proposed = array(
            Entity::OUTER_KEY     => 'OUTER_KEY',
            Entity::INNER_KEY     => 'INNER_KEY',
            Entity::THOUGHT_TABLE => 'THOUGHT',
            Entity::PIVOT_TABLE   => 'PIVOT_TABLE'
        );

        foreach ($proposed as $property => $alias)
        {
            if (isset($this->definition[$property]))
            {
                $options['definition:' . $alias] = $this->definition[$property];
            }
        }

        if ($this->outerEntity())
        {
            $options = $options + array(
                    'outer:roleName'   => $this->outerEntity()->getRoleName(),
                    'outer:table'      => $this->outerEntity()->getTable(),
                    'outer:primaryKey' => $this->outerEntity()->getPrimaryKey()
                );
        }

        return $options;
    }

    /**
     * Relation name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Relation type.
     *
     * @return int
     */
    public function getType()
    {
        return static::RELATION_TYPE;
    }

    /**
     * Relation target class or interface.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Get instance on EntitySchema assosicated with outer entity (presented only for non polymorphic
     * relations).
     *
     * @return null|EntitySchema
     */
    protected function outerEntity()
    {
        return $this->ormSchema->getEntity($this->target);
    }

    /**
     * Relation definition.
     *
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Check if relationship has equivalent based on declared definition, default behaviour will
     * select polymorphic equivalent if target declared as interface.
     *
     * @return bool
     */
    public function hasEquivalent()
    {
        if (!static::EQUIVALENT_RELATION)
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
        unset($definition[static::RELATION_TYPE]);

        return array(static::EQUIVALENT_RELATION => $this->target) + $definition;
    }

    /**
     * Create all required relation columns, indexes and constraints.
     */
    abstract public function buildSchema();

    /**
     * Relation definition contains request to be reverted.
     *
     * @return bool
     */
    public function hasBackReference()
    {
        return isset($this->definition[Entity::BACK_REF]);
    }

    /**
     * Create reverted relations in outer entity or entities.
     *
     * @param string $name Relation name.
     * @param int    $type Back relation type, can be required some cases.
     * @throws ORMException
     */
    abstract public function revertRelation($name, $type = null);
}