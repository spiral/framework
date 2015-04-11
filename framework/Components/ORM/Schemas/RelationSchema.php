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
     * Entity::LOCAL_KEY => '{foreign:roleName}_{foreign:pK}'
     * Entity::FOREIGN_KEY => '{foreign:pK}'
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
        if (!$this->hasEquivalent())
        {
            $this->clarifyDefinition();
        }
    }

    protected function outerEntity()
    {
        return $this->ormSchema->getEntity($this->target);
    }

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
            Entity::OUTER_KEY => 'FOREIGN_KEY',
            Entity::LOCAL_KEY   => 'LOCAL_KEY',
            Entity::LOCAL_TYPE  => 'LOCAL_TYPE',
            Entity::THOUGHT     => 'THOUGHT',
            Entity::PIVOT_TABLE => 'PIVOT_TABLE'
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
                    'foreign:roleName'   => $this->outerEntity()->getRoleName(),
                    'foreign:table'      => $this->outerEntity()->getTable(),
                    'foreign:primaryKey' => $this->outerEntity()->getPrimaryKey()
                );
        }

        return $options;
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
}