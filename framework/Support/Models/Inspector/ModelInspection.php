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
}