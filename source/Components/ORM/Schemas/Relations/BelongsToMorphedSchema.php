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
     * Inverse relation.
     *
     * @throws ORMException
     */
    public function inverseRelation()
    {
        if (
            !is_array($this->definition[ActiveRecord::INVERSE])
            || !isset($this->definition[ActiveRecord::INVERSE][1])
        )
        {
            throw new ORMException(
                "Unable to revert BELONG_TO_MORPHED relation ({$this->model}.{$this->name}), " .
                "back relation type is missing."
            );
        }

        $inversed = $this->definition[ActiveRecord::INVERSE];
        foreach ($this->getOuterModels() as $record)
        {
            $record->addRelation(
                $inversed[1],
                [
                    $inversed[0]            => $this->model->getClass(),
                    ActiveRecord::OUTER_KEY => $this->definition[ActiveRecord::INNER_KEY],
                    ActiveRecord::INNER_KEY => $this->definition[ActiveRecord::OUTER_KEY],
                    ActiveRecord::MORPH_KEY => $this->definition[ActiveRecord::MORPH_KEY],
                    ActiveRecord::NULLABLE  => $this->definition[ActiveRecord::NULLABLE]
                ]
            );
        }
    }

    /**
     * Create all required relation columns, indexes and constraints.
     *
     * @throws ORMException
     */
    public function buildSchema()
    {
        if (!$this->getOuterModels())
        {
            //No targets found, no need to generate anything
            return;
        }

        $innerSchema = $this->model->getTableSchema();

        /**
         * Morph key contains parent type, nullable by default.
         */
        $morphKey = $innerSchema->column($this->getMorphKey());
        $morphKey->string(static::MORPH_COLUMN_SIZE);
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
}