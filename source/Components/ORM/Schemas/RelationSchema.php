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
use Spiral\Components\DBAL\Schemas\AbstractColumnSchema;
use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Relation;
use Spiral\Components\ORM\SchemaBuilder;
use Spiral\Core\Container;

abstract class RelationSchema implements RelationSchemaInterface
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
    const MORPH_COLUMN_SIZE = 32;

    /**
     * Parent ORM schema holds all active record schemas.
     *
     * @invisible
     * @var SchemaBuilder
     */
    protected $builder = null;

    /**
     * Associated active record schema.
     *
     * @invisible
     * @var ModelSchema
     */
    protected $model = null;

    /**
     * Relation name.
     *
     * @var string
     */
    protected $name = '';


    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     * Every parameter described by it's key and pattern.
     *
     * Example:
     * ActiveRecord::INNER_KEY => '{outer:roleName}_{outer:primaryKey}'
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = [];

    /**
     * Relation definition.
     *
     * @var array
     */
    protected $definition = [];

    /**
     * Target model or interface (for polymorphic classes).
     *
     * @var string
     */
    protected $target = '';

    /**
     * New RelationSchema instance.
     *
     * @param SchemaBuilder $builder
     * @param ModelSchema   $model
     * @param string        $name
     * @param array         $definition
     */
    public function __construct(SchemaBuilder $builder, ModelSchema $model, $name, array $definition)
    {
        $this->builder = $builder;
        $this->model = $model;

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
                "Unable to build relation from '{$this->model}' "
                . "to undefined target '{$this->target}'."
            );
        }

        $this->clarifyDefinition();
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

        return (new \ReflectionClass($this->target))->isInterface();
    }

    /**
     * Create equivalent relation.
     *
     * @return RelationSchemaInterface
     * @throws ORMException
     */
    public function createEquivalent()
    {
        $definition = [
                static::EQUIVALENT_RELATION => $this->target
            ] + $this->definition;

        unset($definition[static::RELATION_TYPE]);

        //Usually when relation declared as polymorphic
        return $this->builder->relationSchema($this->model, $this->name, $definition);
    }

    /**
     * Relation definition contains request to be reverted.
     *
     * @return bool
     */
    public function isInversable()
    {
        return isset($this->definition[ActiveRecord::INVERSE]);
    }

    /**
     * Create reverted relations in outer model or models.
     *
     * @throws ORMException
     */
    abstract public function inverseRelation();

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
        $options = [
            'name'              => $this->name,
            'name:plural'       => Inflector::pluralize($this->name),
            'name:singular'     => Inflector::singularize($this->name),
            'record:roleName'   => $this->model->getRoleName(),
            'record:table'      => $this->model->getTable(),
            'record:primaryKey' => $this->model->getPrimaryKey(),
        ];

        $proposed = [
            ActiveRecord::OUTER_KEY   => 'OUTER_KEY',
            ActiveRecord::INNER_KEY   => 'INNER_KEY',
            ActiveRecord::PIVOT_TABLE => 'PIVOT_TABLE'
        ];

        foreach ($proposed as $property => $alias)
        {
            if (isset($this->definition[$property]))
            {
                $options['definition:' . $alias] = $this->definition[$property];
            }
        }

        if ($this->getOuterModel())
        {
            $options = $options + [
                    'outer:roleName'   => $this->getOuterModel()->getRoleName(),
                    'outer:table'      => $this->getOuterModel()->getTable(),
                    'outer:primaryKey' => $this->getOuterModel()->getPrimaryKey()
                ];
        }

        return $options;
    }

    /**
     * Check if relation points to model data from another database. We should not be creating
     * foreign keys in this case.
     *
     * @return bool
     */
    public function isOuterDatabase()
    {
        $outerDatabase = $this->getOuterModel()->getDatabase();

        return $this->model->getDatabase() != $outerDatabase;
    }

    /**
     * Get instance on ModelSchema assosicated with outer active record (presented only for non
     * polymorphic relations).
     *
     * @return null|ModelSchema
     */
    protected function getOuterModel()
    {
        return $this->builder->modelSchema($this->target);
    }

    /**
     * Many relations can be nullable (has no parent) by default, to simplify schema creation.
     *
     * @return bool
     */
    public function isNullable()
    {
        if (array_key_exists(ActiveRecord::NULLABLE, $this->definition))
        {
            return $this->definition[ActiveRecord::NULLABLE];
        }

        return false;
    }

    /**
     * Check if relation requests foreign key constraints to be created.
     *
     * @return bool
     */
    public function isConstrained()
    {
        if ($this->isOuterDatabase())
        {
            //Unable to create constraint when relation points to another database
            return false;
        }

        if (array_key_exists(ActiveRecord::CONSTRAINT, $this->definition))
        {
            return $this->definition[ActiveRecord::CONSTRAINT];
        }

        return false;
    }

    /**
     * Constraint action to be applied to created foreign key.
     *
     * @return string|null
     */
    public function getConstraintAction()
    {
        if (array_key_exists(ActiveRecord::CONSTRAINT_ACTION, $this->definition))
        {
            return $this->definition[ActiveRecord::CONSTRAINT_ACTION];
        }

        return null;
    }

    /**
     * Inner key name.
     *
     * @return null|string
     */
    public function getInnerKey()
    {
        if (isset($this->definition[ActiveRecord::INNER_KEY]))
        {
            return $this->definition[ActiveRecord::INNER_KEY];
        }

        return null;
    }

    /**
     * Abstract type needed to represent inner key (excluding primary keys).
     *
     * @return null|string
     */
    public function getInnerKeyType()
    {
        if (!$innerKey = $this->getInnerKey())
        {
            return null;
        }

        return $this->resolveAbstractType(
            $this->model->getTableSchema()->column($innerKey)
        );
    }

    /**
     * Outer key name.
     *
     * @return null|string
     */
    public function getOuterKey()
    {
        if (isset($this->definition[ActiveRecord::OUTER_KEY]))
        {
            return $this->definition[ActiveRecord::OUTER_KEY];
        }

        return null;
    }

    /**
     * Abstract type needed to represent outer key (excluding primary keys).
     *
     * @return null|string
     */
    public function getOuterKeyType()
    {
        if (!$outerKey = $this->getOuterKey())
        {
            return null;
        }

        return $this->resolveAbstractType(
            $this->getOuterModel()->getTableSchema()->column($outerKey)
        );
    }

    /**
     * Resolve correct abstract type to represent inner or outer key. Primary types will be converted
     * to appropriate sized integers.
     *
     * @param AbstractColumnSchema $column
     * @return string
     */
    protected function resolveAbstractType(AbstractColumnSchema $column)
    {
        switch ($column->abstractType())
        {
            case 'bigPrimary':
                return 'bigInteger';
            case 'primary':
                return 'integer';
            default:
                return $column->abstractType();
        }
    }

    /**
     * Simplified method to cast column type and options by provided definition.
     *
     * @param AbstractColumnSchema $column
     * @param string               $definition
     */
    protected function castColumn(AbstractColumnSchema $column, $definition)
    {
        $validType = preg_match(
            '/(?P<type>[a-z]+)(?: *\((?P<options>[^\)]+)\))?(?: *, *(?P<nullable>null(?:able)?))?/i',
            $definition,
            $matches
        );

        //Parsing definition
        if (!$validType)
        {
            throw new ORMException(
                "Unable to parse definition of pivot column {$this->getName()}.'{$column->getName()}'."
            );
        }

        if (!empty($matches['nullable']))
        {
            //No need to force NOT NULL as this is default column state
            $column->nullable(true);
        }

        $type = $matches['type'];

        $options = [];
        if (!empty($matches['options']))
        {
            $options = array_map('trim', explode(',', $matches['options']));
        }

        call_user_func_array([$column, $type], $options);
    }

    /**
     * Create all required relation columns, indexes and constraints.
     */
    abstract public function buildSchema();

    /**
     * Normalize relation options.
     *
     * @return array
     */
    protected function normalizeDefinition()
    {
        $outerTable = null;
        if (!empty($this->getOuterModel()))
        {
            $outerTable = $this->getOuterModel()->getTable();
        }

        $definition = [Relation::OUTER_TABLE => $outerTable] + $this->definition;

        //Unnecessary fields.
        unset(
            $definition[ActiveRecord::CONSTRAINT],
            $definition[ActiveRecord::CONSTRAINT_ACTION],
            $definition[ActiveRecord::CREATE_PIVOT],
            $definition[ActiveRecord::INVERSE],
            $definition[ActiveRecord::CONSTRAINT_ACTION]
        );

        return $definition;
    }

    /**
     * Pack relation data into normalized structured to be used in cached ORM schema.
     *
     * @return array
     */
    public function normalizeSchema()
    {
        return [
            ORM::R_TYPE       => static::RELATION_TYPE,
            ORM::R_DEFINITION => $this->normalizeDefinition()
        ];
    }
}