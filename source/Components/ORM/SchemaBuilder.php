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
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;
use Spiral\Core\Container;

class SchemaBuilder extends Component
{
    /**
     * ORM class names.
     */
    const DATA_ENTITY   = 'Spiral\Components\DataEntity';
    const ACTIVE_RECORD = 'Spiral\Components\ORM\ActiveRecord';

    /**
     * Mapping used to link relationship definition to relationship schemas.
     *
     * @var array
     */
    protected $relationships = [
        ActiveRecord::BELONGS_TO         => 'Spiral\Components\ORM\Schemas\Relations\BelongsToSchema',
        ActiveRecord::BELONGS_TO_MORPHED => 'Spiral\Components\ORM\Schemas\Relations\BelongsToMorphedSchema',

        ActiveRecord::HAS_ONE            => 'Spiral\Components\ORM\Schemas\Relations\HasOneSchema',
        ActiveRecord::HAS_MANY           => 'Spiral\Components\ORM\Schemas\Relations\HasManySchema',

        ActiveRecord::MANY_TO_MANY       => 'Spiral\Components\ORM\Schemas\Relations\ManyToManySchema',
        ActiveRecord::MANY_TO_MORPHED    => 'Spiral\Components\ORM\Schemas\Relations\ManyToMorphedSchema',

        ActiveRecord::MANY_THOUGHT       => 'Spiral\Components\ORM\Schemas\Relations\ManyThoughtSchema',
    ];

    /**
     * Schema generating configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * DatabaseManager instance.
     *
     * @var DatabaseManager
     */
    protected $dbal = null;

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
     * @param array           $config
     * @param Tokenizer       $tokenizer
     * @param DatabaseManager $dbal
     * @param Container       $contaner
     */
    public function __construct(
        array $config,
        Tokenizer $tokenizer,
        DatabaseManager $dbal,
        Container $container)
    {
        $this->config = $config;
        $this->dbal = $dbal;
        $this->container = $container;

        foreach ($tokenizer->getClasses(self::ACTIVE_RECORD) as $class => $definition)
        {
            if ($class == self::ACTIVE_RECORD)
            {
                continue;
            }

            $this->records[$class] = RecordSchema::make([
                'class'     => $class,
                'ormSchema' => $this
            ], $this->container);
        }

        //TODO: error with nested relations based on non declared auto key

        $relations = [];
        foreach ($this->records as $record)
        {
            if (!$record->isAbstract())
            {
                $record->castRelations();

                foreach ($record->getRelations() as $relation)
                {
                    if ($relation->hasInvertedRelation())
                    {
                        $relations[] = $relation;
                    }
                }
            }
        }

        /**
         * @var RelationSchema $relation
         */
        foreach ($relations as $relation)
        {
            $backReference = $relation->getDefinition()[ActiveRecord::BACK_REF];

            if (is_array($backReference))
            {
                //[TYPE, NAME]
                $relation->revertRelation($backReference[1], $backReference[0]);
            }
            else
            {
                $relation->revertRelation($backReference);
            }
        }
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
     * Get RecordSchema by class name.
     *
     * @param string $class Class name.
     * @return null|RecordSchema
     */
    public function getRecordSchema($class)
    {
        if ($class == self::ACTIVE_RECORD)
        {
            return RecordSchema::make([
                'class'     => self::ACTIVE_RECORD,
                'ormSchema' => $this
            ], $this->container);
        }

        if (!isset($this->records[$class]))
        {
            return null;
        }

        return $this->records[$class];
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

        $table = $this->dbal->db($database)->table($table)->schema();

        return $this->tables[$database . '/' . $table->getName()] = $table;
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
     * Perform schema reflection to database(s). All declared tables will created or altered.
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

        if (!isset($this->relationships[$type]))
        {
            throw new ORMException("Undefined relationship type {$type}.");
        }

        /**
         * @var RelationSchema $relationship
         */
        $relationship = $this->container->get($this->relationships[$type], [
            'ormSchema'    => $this,
            'recordSchema' => $recordSchema,
            'name'         => $name,
            'definition'   => $definition
        ]);

        if ($relationship->hasEquivalent())
        {
            return $this->relationSchema($recordSchema, $name, $relationship->getEquivalentDefinition());
        }

        return $relationship;
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