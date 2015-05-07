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

class HasOneSchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        ActiveRecord::INNER_KEY         => '{record:primaryKey}',
        ActiveRecord::OUTER_KEY         => '{record:roleName}_{definition:INNER_KEY}',
        ActiveRecord::CONSTRAINT        => true,
        ActiveRecord::CONSTRAINT_ACTION => 'CASCADE',
        ActiveRecord::NULLABLE          => true
    );

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        $outerSchema = $this->getOuterRecordSchema()->getTableSchema();

        $outerKey = $outerSchema->column($this->getOuterKey());
        $outerKey->type($this->getInnerKeyType());
        $outerKey->nullable($this->definition[ActiveRecord::NULLABLE]);
        $outerKey->index();

        if ($this->definition[ActiveRecord::CONSTRAINT] && empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $foreignKey = $outerKey->foreign(
                $this->recordSchema->getTable(),
                $this->getInnerKey()
            );

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
        $this->getOuterRecordSchema()->addRelation($name, array(
            ActiveRecord::BELONGS_TO        => $this->recordSchema->getClass(),
            ActiveRecord::INNER_KEY         => $this->definition[ActiveRecord::OUTER_KEY],
            ActiveRecord::OUTER_KEY         => $this->definition[ActiveRecord::INNER_KEY],
            ActiveRecord::CONSTRAINT        => $this->definition[ActiveRecord::CONSTRAINT],
            ActiveRecord::CONSTRAINT_ACTION => $this->definition[ActiveRecord::CONSTRAINT_ACTION],
            ActiveRecord::NULLABLE          => $this->definition[ActiveRecord::NULLABLE]
        ));
    }
}