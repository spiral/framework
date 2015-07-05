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
    protected $defaultDefinition = [
        ActiveRecord::INNER_KEY         => '{record:primaryKey}',
        ActiveRecord::OUTER_KEY         => '{record:roleName}_{definition:INNER_KEY}',
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
        $this->getOuterModel()->addRelation(
            $this->definition[ActiveRecord::INVERSE],
            [
                ActiveRecord::BELONGS_TO        => $this->model->getClass(),
                ActiveRecord::INNER_KEY         => $this->definition[ActiveRecord::OUTER_KEY],
                ActiveRecord::OUTER_KEY         => $this->definition[ActiveRecord::INNER_KEY],
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
        $outerTable = $this->getOuterModel()->getTableSchema();

        //Outer key type should be matched with inner key type
        $outerKey = $outerTable->column($this->getOuterKey());
        $outerKey->type($this->getInnerKeyType());
        $outerKey->nullable($this->isNullable());

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            //We are not going to configure polymorphic relations here
            return;
        }

        //We can safely add index, it will not be created if outer model has passive schema
        $outerKey->index();

        if (!$this->isConstrained())
        {
            return;
        }

        //We are allowed to add foreign key, it will not be created if outer table has passive schema
        $foreignKey = $outerKey->foreign(
            $this->model->getTable(),
            $this->getInnerKey()
        );

        $foreignKey->onDelete($this->getConstraintAction());
        $foreignKey->onUpdate($this->getConstraintAction());
    }
}