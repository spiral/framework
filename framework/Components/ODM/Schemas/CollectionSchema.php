<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM\Schemas;

use Spiral\Components\ODM\SchemaBuilder;
use Spiral\Core\Component;

class CollectionSchema extends Component
{
    /**
     * Parent ODM schema builder holds all other documents.
     *
     * @invisible
     * @var null|SchemaBuilder
     */
    protected $builder = null;

    /**
     * Collection database id.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Collection database id.
     *
     * @var string
     */
    protected $database = '';

    /**
     * How to define valid class declaration based on set of fields fetched from collection, default
     * way is "DEFINITION_FIELDS", this method will define set of unique fields existed in every
     * class. Second option is to define method to resolve class declaration "LOGICAL".
     *
     * @var array
     */
    protected $classDefinition = array();

    /**
     * Primary collection class (first class in extend chain).
     *
     * @var string
     */
    protected $primaryClass = '';

    /**
     * New collection schema.
     *
     * @param SchemaBuilder $builder         ODM schema.
     * @param string        $name            Collection name.
     * @param string        $database        Database name/id.
     * @param array         $classDefinition Class definition technique.
     * @param string        $primaryClass    Primary class name.
     */
    public function __construct(
        SchemaBuilder $builder,
        $name,
        $database,
        $classDefinition,
        $primaryClass = ''
    )
    {
        $this->builder = $builder;
        $this->name = $name;
        $this->database = $database;
        $this->classDefinition = $classDefinition;
        $this->primaryClass = $primaryClass;
    }

    /**
     * Collection database id.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Collection database id.
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * How to define valid class declaration based on set of fields fetched from collection, default
     * way is "DEFINITION_FIELDS", this method will define set of unique fields existed in every class.
     * Second option is to define method to resolve class declaration "DEFINITION_LOGICAL".
     *
     * @return array
     */
    public function classDefinition()
    {
        return $this->classDefinition;
    }

    /**
     * Primary collection class (first class in extend chain).
     *
     * @return string
     */
    public function primaryClass()
    {
        return $this->primaryClass;
    }

    /**
     * Document schema of first document used to create current model. Basically this is first class
     * in extending chain.
     *
     * @return null|DocumentSchema
     */
    public function primaryDocument()
    {
        return $this->builder->getDocument($this->primaryClass);
    }
}