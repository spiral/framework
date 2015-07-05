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
    const TYPE_COLUMN_SIZE = 32;

    /**
     * Parent ORM schema holds all active record schemas.
     *
     * @invisible
     * @var SchemaBuilder
     */
    protected $schemaBuilder = null;

    /**
     * Associated active record schema.
     *
     * @invisible
     * @var RecordSchema
     */
    protected $recordSchema = null;

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
    protected $definition = [];

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
     * Target model or interface (for polymorphic classes).
     *
     * @var string
     */
    protected $target = '';

    /**
     * New RelationSchema instance.
     *
     * @param SchemaBuilder $schemaBuilder
     * @param RecordSchema  $recordSchema
     * @param string        $name
     * @param array         $definition
     */
    public function __construct(
        SchemaBuilder $schemaBuilder,
        RecordSchema $recordSchema,
        $name,
        array $definition
    )
    {
        $this->schemaBuilder = $schemaBuilder;
        $this->recordSchema = $recordSchema;

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
                "Unable to build relation from '{$this->recordSchema}' "
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
        $options = [
            'name'              => $this->name,
            'name:plural'       => Inflector::pluralize($this->name),
            'name:singular'     => Inflector::singularize($this->name),
            'record:roleName'   => $this->recordSchema->getRoleName(),
            'record:table'      => $this->recordSchema->getTable(),
            'record:primaryKey' => $this->recordSchema->getPrimaryKey(),
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

        if ($this->getOuterRecordSchema())
        {
            $options = $options + [
                    'outer:roleName'   => $this->getOuterRecordSchema()->getRoleName(),
                    'outer:table'      => $this->getOuterRecordSchema()->getTable(),
                    'outer:primaryKey' => $this->getOuterRecordSchema()->getPrimaryKey()
                ];
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
     * Get instance on RecordSchema assosicated with outer active record (presented only for non
     * polymorphic relations).
     *
     * @return null|RecordSchema
     */
    protected function getOuterRecordSchema()
    {
        return $this->schemaBuilder->recordSchema($this->target);
    }

    /**
     * Relation definition (declared in model schema).
     *
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
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
            $this->recordSchema->getTableSchema()->column($innerKey)
        );
    }

    /**
     * Outer table name.
     *
     * @return null|string
     */
    public function getOuterTable()
    {
        if ($this->getOuterRecordSchema())
        {
            return $this->getOuterRecordSchema()->getTable();
        }

        return null;
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
            $this->getOuterRecordSchema()->getTableSchema()->column($outerKey)
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
     * Simplified method to cast column by provided definition.
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

        return [static::EQUIVALENT_RELATION => $this->target] + $definition;
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
    public function hasInvertedRelation()
    {
        return isset($this->definition[ActiveRecord::BACK_REF]);
    }

    /**
     * Create reverted relations in outer model or models.
     *
     * @param string $name Relation name.
     * @param int    $type Back relation type, can be required some cases.
     * @throws ORMException
     */
    abstract public function revertRelation($name, $type = null);

    /**
     * Normalize relation options.
     *
     * @return array
     */
    protected function normalizeDefinition()
    {
        $definition = [
                Relation::OUTER_TABLE => $this->getOuterTable()
            ] + $this->definition;

        //Unnecessary fields.
        unset(
            $definition[ActiveRecord::CONSTRAINT],
            $definition[ActiveRecord::CONSTRAINT_ACTION],
            $definition[ActiveRecord::CREATE_PIVOT],
            $definition[ActiveRecord::BACK_REF],
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