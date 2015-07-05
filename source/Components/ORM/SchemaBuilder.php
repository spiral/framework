<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Schemas\AbstractTableSchema;
use Spiral\Components\ORM\Schemas\RecordSchema;
use Spiral\Components\ORM\Schemas\RelationSchema;
use Spiral\Components\ORM\Schemas\RelationSchemaInterface;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Support\Models\DataEntity;

class SchemaBuilder extends Component
{
    /**
     * ORM class names.
     */
    const DATA_ENTITY   = DataEntity::class;
    const ACTIVE_RECORD = ActiveRecord::class;

    /**
     * Schema generating configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * ORM component instance.
     *
     * @var ORM
     */
    protected $orm = null;

    /**
     * Container.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Found entity schemas.
     *
     * @var RecordSchema[]
     */
    protected $records = [];

    /**
     * All declared tables.
     *
     * @var array
     */
    public $tables = [];

    /**
     * New ORM Schema reader instance.
     *
     * @param array     $config
     * @param Tokenizer $tokenizer
     * @param ORM       $orm
     * @param Container $container
     */
    public function __construct(
        array $config,
        Tokenizer $tokenizer,
        ORM $orm,
        Container $container
    )
    {
        $this->config = $config;
        $this->orm = $orm;
        $this->container = $container;

        foreach ($tokenizer->getClasses(self::ACTIVE_RECORD) as $class => $definition)
        {
            if ($class == self::ACTIVE_RECORD)
            {
                continue;
            }

            $this->records[$class] = new RecordSchema($class, $this);
        }

        //TODO: error with nested relations based on non declared auto key

        $invertRelations = [];
        foreach ($this->records as $record)
        {
            if (!$record->isAbstract())
            {
                $record->castRelations();

                foreach ($record->getRelations() as $relation)
                {
                    if ($relation->hasInvertedRelation())
                    {
                        $invertRelations[] = $relation;
                    }
                }
            }
        }

        /**
         * @var RelationSchemaInterface $relation
         */
        foreach ($invertRelations as $relation)
        {
            $inverted = $relation->getDefinition()[ActiveRecord::BACK_REF];

            if (is_array($inverted))
            {
                //TODO: THINK ABOUT IT
                //[TYPE, NAME]
                $relation->revertRelation($inverted[1], $inverted[0]);
            }
            else
            {
                $relation->revertRelation($inverted);
            }
        }
    }

    /**
     * Get RecordSchema by class name.
     *
     * @param string $class Class name.
     * @return null|RecordSchema
     */
    public function recordSchema($class)
    {
        if ($class == self::ACTIVE_RECORD)
        {
            return new RecordSchema(self::ACTIVE_RECORD, $this);
        }

        if (!isset($this->records[$class]))
        {
            return null;
        }

        return $this->records[$class];
    }

    /**
     * All fetched entity schemas.
     *
     * @return RecordSchema[]
     */
    public function getRecordSchemas()
    {
        return $this->records;
    }

    /**
     * Get mutators for column with specified abstract or column type.
     *
     * @param string $abstractType Column type.
     * @return array
     */
    public function getMutators($abstractType)
    {
        return isset($this->config['mutators'][$abstractType])
            ? $this->config['mutators'][$abstractType]
            : [];
    }

    /**
     * Declare table schema to be created.
     *
     * @param string $database
     * @param string $table
     * @return AbstractTableSchema
     */
    public function declareTable($database, $table)
    {
        if (isset($this->tables[$database . '/' . $table]))
        {
            return $this->tables[$database . '/' . $table];
        }

        $schema = $this->orm->getDBAL()->db($database)->table($table)->schema();

        return $this->tables[$database . '/' . $table] = $schema;
    }

    /**
     * Get list of all declared tables. Cascade parameter will sort tables in order of their self
     * dependencies.
     *
     * @param bool $cascade
     * @return AbstractTableSchema[]
     */
    public function getDeclaredTables($cascade = true)
    {
        if ($cascade)
        {
            $tables = $this->tables;
            uasort($tables, function (AbstractTableSchema $tableA, AbstractTableSchema $tableB)
            {
                return in_array($tableA->getName(), $tableB->getDependencies())
                || count($tableB->getDependencies()) > count($tableA->getDependencies());
            });

            return array_reverse($tables);
        }

        return $this->tables;
    }

    /**
     * Get appropriate relation schema based on provided definition.
     *
     * @param RecordSchema $recordSchema
     * @param string       $name
     * @param array        $definition
     * @return RelationSchema
     */
    public function relationSchema(RecordSchema $recordSchema, $name, array $definition)
    {
        if (empty($definition))
        {
            throw new ORMException("Relation definition can not be empty.");
        }

        reset($definition);
        $type = key($definition);

        /**
         * @var RelationSchema $relation
         */
        $relation = $this->orm->relationSchema($type, $this, $recordSchema, $name, $definition);

        if ($relation->hasEquivalent())
        {
            return $this->relationSchema(
                $recordSchema,
                $name,
                $relation->getEquivalentDefinition()
            );
        }

        return $relation;
    }

    /**
     * Perform schema reflection to database(s). All declared tables will created or altered.
     *
     * TODO: REWRITE
     */
    public function executeSchema()
    {
        foreach ($this->tables as $table)
        {
            //TODO: IS ABSTRACT
            foreach ($this->records as $entity)
            {
                if ($entity->getTableSchema() == $table && !$entity->isActiveSchema())
                {
                    //What is going on here?
                    //TODO: BABDBAD!
                }
            }
        }

        foreach ($this->getDeclaredTables(true) as $table)
        {
            $table->save();
        }
    }

    /**
     * Normalize ODM schema and export it to be used by ODM component and all documents.
     *
     * @return array
     */
    public function normalizeSchema()
    {
        $schema = [];

        foreach ($this->records as $record)
        {
            if ($record->isAbstract())
            {
                continue;
            }

            $recordSchema = [];

            $recordSchema[ORM::E_ROLE_NAME] = $record->getRoleName();
            $recordSchema[ORM::E_TABLE] = $record->getTable();
            $recordSchema[ORM::E_DB] = $record->getDatabase();
            $recordSchema[ORM::E_PRIMARY_KEY] = $record->getPrimaryKey();

            $recordSchema[ORM::E_COLUMNS] = $record->getDefaults();
            $recordSchema[ORM::E_HIDDEN] = $record->getHidden();
            $recordSchema[ORM::E_SECURED] = $record->getSecured();
            $recordSchema[ORM::E_FILLABLE] = $record->getFillable();

            $recordSchema[ORM::E_MUTATORS] = $record->getMutators();
            $recordSchema[ORM::E_VALIDATES] = $record->getValidates();

            //Relations
            $recordSchema[ORM::E_RELATIONS] = [];
            foreach ($record->getRelations() as $name => $relation)
            {
                $recordSchema[ORM::E_RELATIONS][$name] = $relation->normalizeSchema();
            }

            ksort($recordSchema);
            $schema[$record->getClass()] = $recordSchema;
        }

        return $schema;
    }
}