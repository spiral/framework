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
use Spiral\Components\ORM\Schemas\RelationSchema;

class BelongsToSchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::BELONGS_TO;


    /**
     * Equivalent relationship resolved based on definition and not schema, usually polymorphic.
     */
    const EQUIVALENT_RELATION = ActiveRecord::BELONGS_TO_MORPHED;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = [
        ActiveRecord::OUTER_KEY         => '{outer:primaryKey}',
        ActiveRecord::INNER_KEY         => '{name:singular}_{definition:OUTER_KEY}',
        ActiveRecord::CONSTRAINT        => true,
        ActiveRecord::CONSTRAINT_ACTION => 'CASCADE',
        ActiveRecord::NULLABLE          => true
    ];

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        $innerSchema = $this->recordSchema->getTableSchema();

        $innerKey = $innerSchema->column($this->getInnerKey());
        $innerKey->type($this->getOuterKeyType());
        $innerKey->nullable($this->isNullable());
        $innerKey->index();

        //We have to define constraint only if it was requested (by default)
        if ($this->definition[ActiveRecord::CONSTRAINT])
        {
            $foreignKey = $innerKey->foreign($this->getOuterTable(), $this->getOuterKey());
            $foreignKey->onDelete($this->definition[ActiveRecord::CONSTRAINT_ACTION]);
            $foreignKey->onUpdate($this->definition[ActiveRecord::CONSTRAINT_ACTION]);
        }
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

        $this->getOuterRecordSchema()->addRelation($name, [
            $type                           => $this->recordSchema->getClass(),
            ActiveRecord::OUTER_KEY         => $this->definition[ActiveRecord::INNER_KEY],
            ActiveRecord::INNER_KEY         => $this->definition[ActiveRecord::OUTER_KEY],
            ActiveRecord::CONSTRAINT        => $this->definition[ActiveRecord::CONSTRAINT],
            ActiveRecord::CONSTRAINT_ACTION => $this->definition[ActiveRecord::CONSTRAINT_ACTION],
            ActiveRecord::NULLABLE          => $this->definition[ActiveRecord::NULLABLE]
        ]);
    }
}