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
use Spiral\Components\ORM\SchemaBuilder;

abstract class MorphedRelationSchema extends RelationSchema
{
    /**
     * Option string used to populate definition template if no user value provided.
     *
     * @return array
     */
    protected function definitionOptions()
    {
        $options = parent::definitionOptions();

        foreach ($this->ormSchema->getEntities() as $entity)
        {
            if ($entity->getReflection()->isSubclassOf($this->target))
            {
                //One model will be enough
                $options['outer:primaryKey'] = $entity->getPrimaryKey();
                break;
            }
        }

        return $options;
    }

    /**
     * Get all relation target classes.
     *
     * @return EntitySchema[]
     */
    public function getOuterEntities()
    {
        $entities = array();
        foreach ($this->ormSchema->getEntities() as $entity)
        {
            if ($entity->getReflection()->isSubclassOf($this->target))
            {
                //One model will be enough
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    /**
     * Abstract type needed to represent outer key (excluding primary keys).
     *
     * @return null|string
     */
    public function getOuterKeyType()
    {
        $outerKeyType = null;
        foreach ($this->getOuterEntities() as $entity)
        {
            if (!$entity->getTableSchema()->hasColumn($this->getOuterKey()))
            {
                throw new ORMException(
                    "Morphed relation requires outer key exists in every entity ({$entity})."
                );
            }

            $entityKeyType = $this->resolveAbstractType(
                $entity->getTableSchema()->column($this->getOuterKey())
            );

            if (is_null($outerKeyType))
            {
                $$outerKeyType = $entityKeyType;
            }

            //Consistency
            if ($outerKeyType != $entityKeyType)
            {
                throw new ORMException(
                    "Morphed relation requires consistent outer key type ({$entity})."
                );
            }
        }

        return $outerKeyType;
    }
}