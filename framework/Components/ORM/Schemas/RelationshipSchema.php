<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas;

use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\SchemaReader;
use Spiral\Core\Container;

class RelationshipSchema
{
    const RELATIONSHIP_TYPE       = null;
    const EQUIVALENT_RELATIONSHIP = null;

    /**
     * Parent ORM schema holds all entity schemas.
     *
     * @invisible
     * @var SchemaReader
     */
    protected $ormSchema = null;

    protected $name = '';

    protected $definition = array();

    protected $target = '';

    public function __construct(SchemaReader $ormSchema, $name, array $definition)
    {
        $this->ormSchema = $ormSchema;
        $this->name = $name;
        $this->definition = $definition;

        $this->target = $definition[static::RELATIONSHIP_TYPE];
    }

    public function getType()
    {
        return static::RELATIONSHIP_TYPE;
    }

    /**
     * Check if relationship has equivalent based on declared definition, default behaviour will
     * select polymorphic equivalent if target declared as interface.
     *
     * @return bool
     */
    public function hasEquivalent()
    {
        if (!static::EQUIVALENT_RELATIONSHIP)
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
        unset($definition[static::RELATIONSHIP_TYPE]);
        $definition[static::EQUIVALENT_RELATIONSHIP] = $this->target;

        return $definition;
    }
}