<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas\Relations;

use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Schemas\MorphedRelationSchema;

class BelongsToMorphedSchema extends MorphedRelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::BELONGS_TO_MORPHED;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = [
        ActiveRecord::OUTER_KEY => '{outer:primaryKey}',
        ActiveRecord::INNER_KEY => '{name:singular}_{definition:OUTER_KEY}',
        ActiveRecord::MORPH_KEY => '{name:singular}_type',
        ActiveRecord::NULLABLE  => true
    ];

    /**
     * Create all required relation columns, indexes and constraints.
     *
     * @throws ORMException
     */
    public function buildSchema()
    {
        if (!$this->getOuterRecords())
        {
            //No targets found, no need to generate anything
            return;
        }

        $innerSchema = $this->recordSchema->getTableSchema();

        /**
         * Morph key contains parent type, nullable by default.
         */
        $morphKey = $innerSchema->column($this->getMorphKey());
        $morphKey->string(static::TYPE_COLUMN_SIZE);
        $morphKey->nullable($this->isNullable());

        /**
         * Inner key contains link to parent outer key (usually id), nullable by default.
         */
        $innerKey = $innerSchema->column($this->getInnerKey());
        $innerKey->type($this->getOuterKeyType());
        $innerKey->nullable($this->isNullable());

        //Required index
        $innerSchema->index($this->getMorphKey(), $this->getInnerKey());
    }

    /**
     * Create reverted relations in outer model or models.
     *
     * @param string $name Relation name.
     * @param int    $type Back relation type, can be required some cases.
     * @throws ORMException
     */
    public function revertRelation($name, $type = null)
    {
        if (empty($type))
        {
            throw new ORMException(
                "Unable to revert BELONG_TO relation ({$this->recordSchema}), " .
                "back relation type is missing."
            );
        }

        foreach ($this->getOuterRecords() as $record)
        {
            $record->addRelation($name, [
                $type                   => $this->recordSchema->getClass(),
                ActiveRecord::OUTER_KEY => $this->definition[ActiveRecord::INNER_KEY],
                ActiveRecord::INNER_KEY => $this->definition[ActiveRecord::OUTER_KEY],
                ActiveRecord::MORPH_KEY => $this->definition[ActiveRecord::MORPH_KEY],
                ActiveRecord::NULLABLE  => $this->definition[ActiveRecord::NULLABLE]
            ]);
        }
    }
}