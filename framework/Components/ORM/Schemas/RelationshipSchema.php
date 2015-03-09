<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas;

use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\SchemaReader;
use Spiral\Core\Component;

class RelationshipSchema extends Component
{
    /**
     * Connection type.
     */
    const CONNECTION_TYPE = null;

    /**
     * Parent entity (entity declares connection).
     *
     * @invisible
     * @var EntitySchema
     */
    protected $entity = null;

    /**
     * ORM schema with all indexed models, their tables and relationships.
     *
     * @invisible
     * @var SchemaReader
     */
    protected $ormSchema = null;

    /**
     * Relationship name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Connection options specified on schema level. This options will be used to populate column names and etc instead
     * of default way.
     *
     * @var array
     */
    protected $options = array();

    /**
     * New RelationshipSchema instance. Real schema mapping, column resolution and foreign key definition will happen
     * on later stage "dating".
     *
     * @param SchemaReader $ormSchema ORM schema with all indexed models and etc.
     * @param EntitySchema $entity    Parent entity schema.
     * @param string       $name      Relationship name.
     * @param array        $options   Relationship defined options (column names and etc).
     */
    public function __construct(SchemaReader $ormSchema, EntitySchema $entity, $name, array $options = array())
    {
        $this->ormSchema = $ormSchema;
        $this->entity = $entity;
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * Connection type, method used to locate inverse connections.
     *
     * @return int
     */
    public function getType()
    {
        return static::CONNECTION_TYPE;
    }
}