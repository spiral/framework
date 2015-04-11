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

abstract class MorphedRelationSchema extends RelationSchema
{
    /**
     * All entities relation is related to.
     *
     * @var EntitySchema[]
     */
    protected $targets = array();

    protected $outerPrimaryKey = null;
    protected $outerPrimaryAbstractType = null;

    public function __construct(
        SchemaReader $ormSchema,
        EntitySchema $entitySchema,
        $name,
        array $definition
    )
    {
        $this->ormSchema = $ormSchema;
        $this->target = $definition[static::RELATION_TYPE];
        $this->findTargets();

        parent::__construct($ormSchema, $entitySchema, $name, $definition);
    }

    /**
     * Collect all possible relation targets and check their for consistency.
     *
     * @throws ORMException
     */
    protected function findTargets()
    {
        foreach ($this->ormSchema->getEntities() as $entity)
        {
            if ($entity->getReflection()->isSubclassOf($this->target))
            {
                $this->targets[] = $entity;

                if (is_null($this->outerPrimaryKey))
                {
                    $this->outerPrimaryKey = $entity->getPrimaryKey();
                    $this->outerPrimaryAbstractType = $entity->getPrimaryAbstractType();
                }

                //Consistency
                if (
                    $this->outerPrimaryKey != $entity->getPrimaryKey()
                    || $this->outerPrimaryAbstractType != $entity->getPrimaryAbstractType()
                )
                {
                    throw new ORMException(
                        "Morphed relation requires consistent primary key name and type ({$entity})."
                    );
                }
            }
        }
    }

    protected function definitionOptions()
    {
        $options = parent::definitionOptions();
        $options['outer:primaryKey'] = $this->outerPrimaryKey;

        return $options;
    }

    /**
     * Get all relation target classes.
     *
     * @return EntitySchema[]
     */
    public function getTargets()
    {
        return $this->targets;
    }
}