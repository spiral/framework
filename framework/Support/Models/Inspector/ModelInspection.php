<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models\Inspector;

use Spiral\Core\Component;
use Spiral\Support\Models\Schemas\ModelSchema;

class ModelInspection extends Component
{
    /**
     * Model schema.
     *
     * @var ModelSchema
     */
    protected $schema = null;

    /**
     * Field inspections.
     *
     * @var FieldInspection[]
     */
    protected $fields = array();

    /**
     * New model inspection instance.
     *
     * @param ModelSchema $schema
     */
    public function __construct(ModelSchema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Get associated model schema.
     *
     * @return ModelSchema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get model fields inspections.
     *
     * @return FieldInspection[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Analyze model fields and etc.
     *
     * @param array $blacklist List of blacklisted keywords indicates that field has to be hidden
     *                         from publicFields() result.
     */
    public function inspect(array $blacklist)
    {
        $this->fields = array();

        foreach ($this->schema->getFields() as $field)
        {
        }
    }
}