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
                "Unable to revert BELONG_TO relation ({$this->model}.{$this->name}), " .
                "back relation type is missing."
            );
        }

        $inversed = $this->definition[ActiveRecord::INVERSE];

        $this->getOuterModel()->addRelation(
            $inversed[1],
            [
                $inversed[0]                    => $this->model->getClass(),
                ActiveRecord::OUTER_KEY         => $this->definition[ActiveRecord::INNER_KEY],
                ActiveRecord::INNER_KEY         => $this->definition[ActiveRecord::OUTER_KEY],
                ActiveRecord::CONSTRAINT        => $this->definition[ActiveRecord::CONSTRAINT],
                ActiveRecord::CONSTRAINT_ACTION => $this->definition[ActiveRecord::CONSTRAINT_ACTION],
                ActiveRecord::NULLABLE          => $this->definition[ActiveRecord::NULLABLE]
            ]
        );
    }

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        $innerTable = $this->model->getTableSchema();

        //Inner key type should match outer key type
        $innerKey = $innerTable->column($this->getInnerKey());
        $innerKey->type($this->getOuterKeyType());
        $innerKey->nullable($this->isNullable());

        //We can safely add index, it will not be created if outer model has passive schema
        $innerKey->index();

        if (!$this->isConstrained())
        {
            return;
        }

        //We are allowed to add foreign key, it will not be created if outer table has passive schema
        $foreignKey = $innerKey->foreign(
            $this->getOuterModel()->getTable(),
            $this->getOuterKey()
        );

        $foreignKey->onDelete($this->getConstraintAction());
        $foreignKey->onUpdate($this->getConstraintAction());
    }
}