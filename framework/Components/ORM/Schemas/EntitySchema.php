<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas;

use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Schemas\BaseColumnSchema;
use Spiral\Components\DBAL\Schemas\BaseTableSchema;
use Spiral\Components\DBAL\SqlFragment;
use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\SchemaReader;
use Spiral\Core\Component;

class EntitySchema extends Component
{
    /**
     * Some warnings.
     */
    use Component\LoggerTrait;

    /**
     * Entity model class name.
     *
     * @var string
     */
    protected $class = '';

    /**
     * Parent ORM schema holds all other documents.
     *
     * @invisible
     * @var SchemaReader
     */
    protected $ormSchema = null;

    /**
     * Entity model reflection.
     *
     * @var null|\ReflectionClass
     */
    protected $reflection = null;

    /**
     * Cache to speed up schema building.
     *
     * @var array
     */
    protected $propertiesCache = array();

    /**
     * Table schema used to fetch information about declared or fetched columns. Empty if entity is abstract.
     *
     * @var BaseTableSchema
     */
    protected $tableSchema = null;

    /**
     * New EntitySchema instance, schema responsible for detecting relationships, columns and indexes. This class is really
     * similar to DocumentSchema and can be merged into common parent in future.
     *
     * @param string          $class     Class name.
     * @param SchemaReader    $ormSchema Parent ORM schema (all other documents).
     * @param DatabaseManager $dbal      DatabaseManager component.
     */
    public function __construct($class, SchemaReader $ormSchema, DatabaseManager $dbal)
    {
        $this->class = $class;
        $this->ormSchema = $ormSchema;
        $this->reflection = new \ReflectionClass($class);

        if (!$this->isAbstract() && $this->getTable() && $this->getSchema())
        {
            $this->tableSchema = $dbal->db($this->getDatabase())->table($this->getTable())->schema();
            $this->castTable();
        }
    }

    /**
     * Checks if class is abstract.
     *
     * @return bool
     */
    public function isAbstract()
    {
        return $this->reflection->isAbstract();
    }

    /**
     * Entity full class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Entity namespace. Both start and end namespace separators will be removed, to add start separator (absolute)
     * namespace use method parameter "absolute".
     *
     * @param bool $absolute \\ will be prepended to namespace if true, disabled by default.
     * @return string
     */
    public function getNamespace($absolute = false)
    {
        return ($absolute ? '\\' : '') . trim($this->reflection->getNamespaceName(), '\\');
    }

    /**
     * Entity class name without included namespace.
     *
     * @return string
     */
    public function shortName()
    {
        $names = explode('\\', $this->class);

        return end($names);
    }

    /**
     * Getting name should be used to represent entity relationship in foreign classes (default behaviour).
     *
     * Example:
     * Models\Post => HAS_ONE => post_id
     *
     * @return string
     */
    public function roleName()
    {
        return lcfirst($this->shortName());
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

        if ($merge && ($this->reflection->getParentClass()->getName() != SchemaReader::ENTITY))
        {
            $parentClass = $this->reflection->getParentClass()->getName();
            $value = array_merge($this->ormSchema->getEntity($parentClass)->property($property, true), $value);
        }

        return $this->propertiesCache[$property] = call_user_func(array($this->getClass(), 'describeProperty'), $this, $property, $value);
    }

    /**
     * Get table name associated with entity model.
     *
     * @return mixed
     */
    public function getTable()
    {
        return $this->property('table');
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
     * Entity default values. No typecast here as it will be resolved on TableSchema level.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->property('defaults', true);
    }

    /**
     * Get associated table schema. Every column declaration will be presented in this table. Foreign keys are populated
     * automatically on later stage and may not be presented in schema.
     *
     * @return BaseTableSchema
     */
    public function tableSchema()
    {
        return $this->tableSchema;
    }

    /**
     * Getting all secured fields.
     *
     * @return array
     */
    public function getSecured()
    {
        return $this->property('secured', true);
    }

    /**
     * Getting all assignable fields.
     *
     * @return array
     */
    public function getAssignable()
    {
        return $this->property('assignable', true);
    }

    /**
     * Getting all hidden fields.
     *
     * @return array
     */
    public function getHidden()
    {
        return $this->property('hidden', true);
    }

    /**
     * Fill table schema with declared columns, their default values and etc.
     */
    protected function castTable()
    {
        $defaults = $this->getDefaults();
        foreach ($this->getSchema() as $name => $definition)
        {
            //Column definition
            if (is_string($definition) && $definition != Entity::POLYMORPHIC)
            {
                //Filling column values
                $defaults[$name] = $this->castColumn(
                    $column = $this->tableSchema->column($name),
                    $definition,
                    isset($defaults[$name]) ? $defaults[$name] : null
                );
            }

            //Relation definition
            $this->castRelation($name, $definition);
        }

        //Indexes
        foreach ($this->property('indexes', true) as $index)
        {
        }
    }

    /**
     * Cast column schema based on provided column definition and default value. If default value is not clarified and
     * target table is already created, "null" flag will be automatically forced to prevent potential problems.
     * Method will return default value as result.
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
     * @param BaseColumnSchema $column
     * @param string           $definition
     * @param mixed            $default Declared default value or null.
     * @return mixed
     * @throws ORMException
     */
    protected function castColumn(BaseColumnSchema $column, $definition, $default = null)
    {
        if (!is_null($default))
        {
            $column->defaultValue($default);
        }

        //Parsing definition
        if (!preg_match('/(?P<type>[a-z]+)(?: *\((?P<options>[^\)]+)\))?(?: *, *(?P<nullable>null(?:able)?))?/i', $definition, $matches))
        {
            throw new ORMException("Unable to parse definition of  column {$this->getClass()}.'{$column->getName()}'.");
        }

        //No need to force NOT NULL as this is default column state
        !empty($matches['nullable']) && $column->nullable(true);

        $type = $matches['type'];

        $options = array();
        if (!empty($matches['options']))
        {
            $options = array_map('trim', explode(',', $matches['options']));
        }

        //DBAL will handle the rest of declaration
        call_user_func_array(array($column, $type), $options);

        $default = $column->getDefaultValue();
        if ($default instanceof SqlFragment)
        {
            //Potentially
            $default = null;
        }

        if (!$default && !$column->isNullable())
        {
            //As no default value provided and column can not be null we can cast value by ourselves
            if ($column->abstractType() == 'timestamp' || $column->abstractType() == 'datetime')
            {
                $driver = $this->tableSchema->getDriver();

                $default = strtr(
                    $driver::DATETIME,
                    array('Y' => '0000', 'm' => '00', 'd' => '00', 'H' => '00', 'i' => '00', 's' => '00')
                );
            }
            else
            {
                switch ($column->phpType())
                {
                    case 'int':
                        $default = 0;
                        break;
                    case 'float':
                        $default = 0.0;
                        break;
                    case 'bool':
                        $default = false;
                        break;
                    case 'string':
                        $default = '';
                        break;
                }
            }
        }

        return $default;
    }

    /**
     * Create index in associated table based on index definition provided in model or model parent. Attentions, this
     * method is not support to work with primary indexes (for now). Additionally, some relationships will create
     * indexes automatically while defining foreign key.
     *
     * Example:
     * protected $indexes = array(
     *      [self::UNIQUE, 'email'],
     *      [self::INDEX, 'status', 'balance'],
     *      [self::INDEX, 'public_id']
     * );
     *
     * @param array $definition
     */
    protected function castIndex(array $definition)
    {
    }

    protected function castRelation($name, $definition)
    {
    }
}