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
use Spiral\Components\ORM\Schemas\EntitySchema;
use Spiral\Components\ORM\Schemas\RelationshipSchema;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;
use Spiral\Core\Container;

class SchemaReader extends Component
{
    /**
     * ORM class names.
     */
    const DATA_ENTITY = 'Spiral\Components\DataEntity';
    const ENTITY      = 'Spiral\Components\ORM\Entity';

    /**
     * Mapping used to link relationship definition to relationship schemas.
     *
     * @todo: think about morphed word
     * @var array
     */
    protected $relationships = array(
        Entity::HAS_ONE              => 'Spiral\Components\ORM\Schemas\Relationships\HasOneSchema',
        Entity::HAS_MANY             => 'Spiral\Components\ORM\Schemas\Relationships\HasManySchema',
        Entity::BELONGS_TO           => 'Spiral\Components\ORM\Schemas\Relationships\BelongsToSchema',
        Entity::MANY_TO_MANY         => 'Spiral\Components\ORM\Schemas\Relationships\ManyToManySchema',
        Entity::MANY_THOUGHT         => 'Spiral\Components\ORM\Schemas\Relationships\ManyThoughtSchema',

        Entity::BELONGS_TO_MORPHED   => 'Spiral\Components\ORM\Schemas\Relationships\BelongsToMorphedSchema',
        Entity::MANY_TO_MANY_MORPHED => 'Spiral\Components\ORM\Schemas\Relationships\ManyToManyMorphedSchema',

        Entity::MORPHED_HAS_ONE      => 'Spiral\Components\ORM\Schemas\Relationships\MorphedHasOneSchema',
        Entity::MORPHED_HAS_MANY     => 'Spiral\Components\ORM\Schemas\Relationships\MorphedHasManySchema',
        Entity::MORPHED_MANY_TO_MANY => 'Spiral\Components\ORM\Schemas\Relationships\MorphedManyToManySchema'
    );

    /**
     * Schema generating configuration.
     *
     * @var array
     */
    protected $config = array();

    /**
     * DatabaseManager instance.
     *
     * @var DatabaseManager
     */
    protected $dbal = null;

    /**
     * Found entity schemas.
     *
     * @var array
     */
    protected $entities = array();

    public $tables = array();

    /**
     * New ORM Schema reader instance.
     *
     * @param array           $config
     * @param Tokenizer       $tokenizer
     * @param DatabaseManager $dbal
     */
    public function __construct(array $config, Tokenizer $tokenizer, DatabaseManager $dbal)
    {
        $this->config = $config;
        $this->dbal = $dbal;

        foreach ($tokenizer->getClasses(self::ENTITY) as $class => $definition)
        {
            if ($class == self::ENTITY)
            {
                continue;
            }

            $this->entities[$class] = EntitySchema::make(array(
                'class'     => $class,
                'ormSchema' => $this
            ));
        }

        foreach ($this->entities as $e)
        {
            $e->castRelationships();
        }
    }

    //TODO: Add method to get table schema

    /**
     * All fetched entity schemas.
     *
     * @return EntitySchema[]
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Get EntitySchema by class name.
     *
     * @param string $class Class name.
     * @return null|EntitySchema
     */
    public function getEntity($class)
    {
        if ($class == self::ENTITY)
        {
            return EntitySchema::make(array(
                'class'     => self::ENTITY,
                'ormSchema' => $this
            ));
        }

        if (!isset($this->entities[$class]))
        {
            return null;
        }

        return $this->entities[$class];
    }

    public function getTableSchema($database, $table)
    {
        $this->tables[] = $table = $this->dbal->db($database)->table($table)->schema();

        return $table;
    }

    public function getTableSchemas()
    {
        return $this->tables;
    }

    /**
     * Get instance of relationship schema associated with provided definition. Relationship schema
     * may be different than defined in class schema, for example some relationships may change
     * their type to polymorphic form if foreign target declared as interface.
     *
     * @param string $name Relationship name.
     * @param array  $definition
     * @return RelationshipSchema
     */
    public function getRelationshipSchema($name, array $definition)
    {
        if (empty($definition))
        {
            throw new ORMException("Relationship definition can not be empty.");
        }

        reset($definition);
        $type = key($definition);

        if (!isset($this->relationships[$type]))
        {
            throw new ORMException("Undefined relationship type {$type}.");
        }

        /**
         * @var RelationshipSchema $relationship
         */
        $relationship = Container::get($this->relationships[$type], array(
            'ormSchema'  => $this,
            'name'       => $name,
            'definition' => $definition
        ));

        if ($relationship->hasEquivalent())
        {
            return $this->getRelationshipSchema($name, $relationship->getEquivalentDefinition());
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
            : array();
    }
}