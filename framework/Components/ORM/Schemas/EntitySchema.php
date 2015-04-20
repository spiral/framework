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
use Spiral\Components\DBAL\Schemas\AbstractTableSchema;
use Spiral\Components\DBAL\SqlFragmentInterface;
use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\ORMAccessor;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\SchemaBuilder;
use Spiral\Core\Component;
use Spiral\Support\Models\DataEntity;
use Spiral\Support\Models\Schemas\ModelSchema;

class EntitySchema extends ModelSchema
{
    /**
     * Logging.
     */
    use Component\LoggerTrait;

    /**
     * Base model class.
     */
    const BASE_CLASS = SchemaBuilder::ENTITY;

    /**
     * Entity model class name.
     *
     * @var string
     */
    protected $class = '';

    /**
     * Parent ORM schema holds all other entities schema.
     *
     * @invisible
     * @var SchemaBuilder
     */
    protected $ormSchema = null;

    /**
     * Table schema used to fetch information about declared or fetched columns. Empty if entity is
     * abstract.
     *
     * @var AbstractTableSchema
     */
    protected $tableSchema = null;

    /**
     * Model relationships.
     *
     * @var array
     */
    protected $relations = array();

    /**
     * Column names associated with their default values.
     *
     * @var array
     */
    protected $columns = array();

    /**
     * New EntitySchema instance, schema responsible for detecting relationships, columns and indexes.
     * This class is really similar to DocumentSchema and can be merged into common parent in future.
     *
     * @param string        $class     Class name.
     * @param SchemaBuilder $ormSchema Parent ORM schema (all other documents).
     */
    public function __construct($class, SchemaBuilder $ormSchema)
    {
        $this->class = $class;
        $this->ormSchema = $ormSchema;
        $this->reflection = new \ReflectionClass($class);

        $this->tableSchema = $this->ormSchema->declareTable(
            $this->getDatabase(),
            $this->getTable()
        );

        //Casting table columns, indexes, foreign keys and etc
        $this->castTableSchema();
    }

    /**
     * Get name should be used to represent entity relationship in foreign classes (default behaviour).
     *
     * Example:
     * Models\Post => HAS_ONE => post_id
     *
     * @return string
     */
    public function getRoleName()
    {
        return lcfirst($this->getShortName());
    }

    /**
     * True if entity allowed schema modifications.
     *
     * @return bool
     */
    public function isActiveSchema()
    {
        return $this->reflection->getConstant('ACTIVE_SCHEMA');
    }

    /**
     * Get associated table schema. Result can be empty if models is abstract or schema is empty.
     *
     * @return AbstractTableSchema|null
     */
    public function getTableSchema()
    {
        return $this->tableSchema;
    }

    /**
     * Reading default model property value, will read "protected" and "private" properties.
     *
     * @param string $property Property name.
     * @param bool   $merge    If true value will be merged with all parent declarations.
     * @return mixed
     */
    protected function property($property, $merge = false)
    {
        if (isset($this->propertiesCache[$property]))
        {
            return $this->propertiesCache[$property];
        }

        $defaults = $this->reflection->getDefaultProperties();
        if (isset($defaults[$property]))
        {
            $value = $defaults[$property];
        }
        else
        {
            return null;
        }

        if ($merge && ($this->reflection->getParentClass()->getName() != SchemaBuilder::ENTITY))
        {
            $parentClass = $this->reflection->getParentClass()->getName();
            if (is_array($value))
            {
                $value = array_merge(
                    $this->ormSchema->getEntity($parentClass)->property($property, true),
                    $value
                );
            }
        }

        return $this->propertiesCache[$property] = call_user_func(
            array($this->getClass(), 'describeProperty'),
            $this,
            $property,
            $value
        );
    }

    /**
     * Get table name associated with entity model.
     *
     * @return mixed
     */
    public function getTable()
    {
        $table = $this->property('table');

        if (empty($table))
        {
            //We can guess table name
            $table = $this->reflection->getShortName();
            $table = Inflector::tableize($table);

            //Table names are plural by default
            return Inflector::pluralize($table);
        }

        return $table;
    }

    /**
     * Get database model data should be stored in.
     *
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->property('database');
    }

    /**
     * Get entity declared schema (merged with parent model(s) values).
     *
     * @return array
     */
    public function getSchema()
    {
        return $this->property('schema', true);
    }

    /**
     * Get declared indexes. This is not the same set of indexes which can be presented in table
     * schema, use EntitySchema->getTableSchema()->getIndexes() method for it.
     *
     * @see getTableSchema()
     * @return array
     */
    public function getIndexes()
    {
        return $this->property('indexes', true);
    }

    /**
     * Get column names associated with their default values.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->columns;
    }

    /**
     * Fields associated with their type.
     *
     * @return array
     */
    public function getFields()
    {
        $result = array();
        foreach ($this->tableSchema->getColumns() as $column)
        {
            $result[$column->getName()] = $column->phpType();
        }

        return $result;
    }

    /**
     * Find all field mutators.
     *
     * @return mixed
     */
    public function getMutators()
    {
        $mutators = parent::getMutators();

        //Default values.
        foreach ($this->tableSchema->getColumns() as $field => $column)
        {
            $type = $column->abstractType();

            $resolved = array();
            if ($filter = $this->ormSchema->getMutators($type))
            {
                $resolved += $filter;
            }
            elseif ($filter = $this->ormSchema->getMutators('php:' . $column->phpType()))
            {
                $resolved += $filter;
            }

            if (isset($resolved['accessor']))
            {
                //Ensuring type for accessor
                $resolved['accessor'] = array($resolved['accessor'], $type);
            }

            foreach ($resolved as $mutator => $filter)
            {
                if (!array_key_exists($field, $mutators[$mutator]))
                {
                    $mutators[$mutator][$field] = $filter;
                }
            }
        }

        return $mutators;
    }

    /**
     * Name of first primary key (usually sequence).
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return array_slice($this->tableSchema->getPrimaryKeys(), 0, 1)[0];
    }

    /**
     * Fill table schema with declared columns, their default values and etc.
     */
    protected function castTableSchema()
    {
        $this->columns = $this->property('defaults', true);
        foreach ($this->property('schema', true) as $name => $definition)
        {
            //Column definition
            if (is_string($definition))
            {
                //Filling column values
                $this->columns[$name] = $this->castColumn(
                    $this->tableSchema->column($name),
                    $definition,
                    isset($this->columns[$name]) ? $this->columns[$name] : null
                );

                //Preparing default value to be stored in cache
                $this->columns[$name] = $this->prepareDefault($name, $this->columns[$name]);
            }
        }

        //We can cast declared indexes there, however some relationships may cast more indexes
        foreach ($this->getIndexes() as $definition)
        {
            $this->castIndex($definition);
        }
    }

    /**
     * Cast column schema based on provided column definition and default value. Spiral will force
     * default values (internally) for every NOT NULL column except primary keys.
     *
     * Column definition examples (by default all columns has flag NOT NULL):
     * id           => primary
     * name         => string       [default 255 symbols]
     * email        => string(255), nullable
     * status       => enum(active, pending, disabled)
     * balance      => decimal(10, 2)
     * message      => text, null[able]
     * time_expired => timestamp
     *
     * @param AbstractColumnSchema $column
     * @param string               $definition
     * @param mixed                $default Declared default value or null.
     * @return mixed
     * @throws ORMException
     */
    protected function castColumn(AbstractColumnSchema $column, $definition, $default = null)
    {
        if (!is_null($default))
        {
            $column->defaultValue($default);
        }

        $validType = preg_match(
            '/(?P<type>[a-z]+)(?: *\((?P<options>[^\)]+)\))?(?: *, *(?P<nullable>null(?:able)?))?/i',
            $definition,
            $matches
        );

        //Parsing definition
        if (!$validType)
        {
            throw new ORMException(
                "Unable to parse definition of  column {$this->getClass()}.'{$column->getName()}'."
            );
        }

        if (!empty($matches['nullable']))
        {
            //No need to force NOT NULL as this is default column state
            $column->nullable(true);
        }

        $type = $matches['type'];

        $options = array();
        if (!empty($matches['options']))
        {
            $options = array_map('trim', explode(',', $matches['options']));
        }

        //DBAL will handle the rest of declaration
        call_user_func_array(array($column, $type), $options);

        $default = $column->getDefaultValue();

        if ($default instanceof SqlFragmentInterface)
        {
            //We have to rebuild default type in scalar form
            $default = null;
        }

        if (empty($default) && in_array($column->getName(), $this->tableSchema->getPrimaryKeys()))
        {
            return null;
        }

        //We have to cast default value to prevent errors
        if (empty($default) && !$column->isNullable())
        {
            $default = $this->castDefaultValue($column);
            $column->defaultValue($default);
        }

        return $default;
    }

    /**
     * Cast default value based on column type.
     *
     * @param AbstractColumnSchema $column
     * @return bool|float|int|mixed|string
     */
    protected function castDefaultValue(AbstractColumnSchema $column)
    {
        //As no default value provided and column can not be null we can cast value by ourselves
        if ($column->abstractType() == 'timestamp' || $column->abstractType() == 'datetime')
        {
            $driver = $this->tableSchema->getDriver();

            return $driver::DEFAULT_DATETIME;
        }
        else
        {
            switch ($column->phpType())
            {
                case 'int':
                    return 0;
                    break;
                case 'float':
                    return 0.0;
                    break;
                case 'bool':
                    return false;
                    break;
            }
        }

        return '';
    }

    protected function prepareDefault($name, $defaultValue = null)
    {
        if (array_key_exists($name, $this->getAccessors()))
        {
            $accessor = $this->getAccessors()[$name];
            $option = null;
            if (is_array($accessor))
            {
                list($accessor, $option) = $accessor;
            }

            /**
             * @var ORMAccessor $accessor
             */
            $accessor = new $accessor($defaultValue, null, $option);

            //We have to pass default value thought accessor
            return $accessor->defaultValue($this->tableSchema->getDriver());
        }

        if (array_key_exists($name, $this->getSetters()))
        {
            $setter = $this->getSetters()[$name];

            if (is_string($setter) && isset(Entity::$mutatorAliases[$setter]))
            {
                $setter = DataEntity::$mutatorAliases[$setter];
            }

            //We have to pass default value thought accessor
            return call_user_func($setter, $defaultValue);
        }
    }

    /**
     * Create index in associated table based on index definition provided in model or model parent.
     * Attention, this method does not support primary indexes (for now). Additionally, some
     * relationships will create indexes automatically while defining foreign key.
     *
     * Examples:
     * protected $indexes = array(
     *      [self::UNIQUE, 'email'],
     *      [self::INDEX, 'status', 'balance'],
     *      [self::INDEX, 'public_id']
     * );
     *
     * @param array $definition
     * @throws ORMException
     */
    protected function castIndex(array $definition)
    {
        $type = null;
        $columns = array();

        foreach ($definition as $chunk)
        {
            if ($chunk == Entity::INDEX || $chunk == Entity::UNIQUE)
            {
                $type = $chunk;
                continue;
            }

            if (!$this->tableSchema->hasColumn($chunk))
            {
                throw new ORMException("Model {$this->getClass()} has index with undefined column.");
            }

            $columns[] = $chunk;
        }

        if (empty($type))
        {
            throw new ORMException("Model {$this->getClass()} has index with unspecified type.");
        }

        //Defining index
        $this->tableSchema->index($columns)->unique($type == Entity::UNIQUE);
    }

    /**
     * Casting entity relationships.
     */
    public function castRelations()
    {
        foreach ($this->property('schema', true) as $name => $definition)
        {
            if (is_string($definition))
            {
                //Column definition
                continue;
            }

            $this->addRelation($name, $definition);
        }
    }

    /**
     * Get all declared entity relations.
     *
     * @return RelationSchema[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Add relation to EntitySchema.
     *
     * @param string $name
     * @param array  $definition
     */
    public function addRelation($name, array $definition)
    {
        if (isset($this->relations[$name]))
        {
            self::logger()->warning(
                "Unable to create relation '{class}'.'{name}', connection already exists.",
                array(
                    'name'  => $name,
                    'class' => $this->getClass()
                ));

            return;
        }

        $relationship = $this->ormSchema->relationSchema($this, $name, $definition);

        //Initiating required columns, foreign keys and indexes
        $relationship->buildSchema($this);
        $this->relations[$name] = $relationship;
    }
}