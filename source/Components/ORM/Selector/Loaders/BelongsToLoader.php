<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector\Loaders;

use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\Selector;

class BelongsToLoader extends HasOneLoader
{
    /**
     * Relation type is required to correctly resolve foreign model.
     */
    const RELATION_TYPE = ActiveRecord::BELONGS_TO;

    /**
     * Default load method (inload or postload).
     */
    const LOAD_METHOD = Selector::POSTLOAD;

    /**
     * Parse single result row, should fetch related model fields and run nested loader parsers.
     *
     * @param array $row
     * @return mixed
     */
    public function parseRow(array $row)
    {
        $data = $this->fetchData($row);

        if (!$referenceCriteria = $this->fetchReferenceCriteria($data))
        {
            //Relation not loaded
            return;
        }

        if ($this->deduplicate($data))
        {
            //Clarifying parent dataset
            $this->collectReferences($data);
        }

        if ($this->options['method'] == Selector::INLOAD)
        {
            $this->parent->mount(
                $this->container,
                $this->getReferenceKey(),
                $referenceCriteria,
                $data
            );
        }
        else
        {
            $this->parent->mountOuter(
                $this->container,
                $this->getReferenceKey(),
                $referenceCriteria,
                $data
            );
        }

        $this->parseNested($row);
    }
}