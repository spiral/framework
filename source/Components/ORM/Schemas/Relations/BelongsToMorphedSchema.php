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
    protected $defaultDefinition = array(
        ActiveRecord::OUTER_KEY => '{outer:primaryKey}',
        ActiveRecord::INNER_KEY => '{name:singular}_{definition:OUTER_KEY}',
        ActiveRecord::MORPH_KEY => '{name:singular}_type',
        ActiveRecord::NULLABLE  => true
    );

    /**
     * Create all required relation columns, indexes and constraints.
     *
     * @throws ORMException
     */
    public function buildSchema()
    {
        if (!$this->getOuterRecordSchemas())
        {
            //No targets found, no need to generate anything
            return;
        }

        $innerSchema = $this->recordSchema->getTableSchema();

        $morphKey = $innerSchema->column($this->definition[ActiveRecord::MORPH_KEY]);
        $morphKey->string(static::TYPE_COLUMN_SIZE);

        $innerKey = $innerSchema->column($this->getInnerKey());
        $innerKey->type($this->getOuterKeyType());
        $innerKey->nullable($this->definition[ActiveRecord::NULLABLE]);

        $innerSchema->index(
            $this->definition[ActiveRecord::MORPH_KEY],
            $this->definition[ActiveRecord::INNER_KEY]
        );
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

        foreach ($this->getOuterRecordSchemas() as $record)
        {
            $record->addRelation($name, array(
                $type                   => $this->recordSchema->getClass(),
                ActiveRecord::OUTER_KEY => $this->definition[ActiveRecord::INNER_KEY],
                ActiveRecord::INNER_KEY => $this->definition[ActiveRecord::OUTER_KEY],
                ActiveRecord::MORPH_KEY => $this->definition[ActiveRecord::MORPH_KEY],
                ActiveRecord::NULLABLE  => $this->definition[ActiveRecord::NULLABLE]
            ));
        }
    }
}