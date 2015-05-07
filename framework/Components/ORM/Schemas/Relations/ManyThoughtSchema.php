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

//TODO: REWRITE ENTIRELLY
class ManyThoughtSchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::MANY_THOUGHT;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        ActiveRecord::INNER_KEY         => '{record:primaryKey}',
        ActiveRecord::OUTER_KEY         => '{outer:primaryKey}',
        ActiveRecord::THOUGHT_INNER_KEY => '{record:roleName}_{definition:INNER_KEY}',
        ActiveRecord::THOUGHT_OUTER_KEY => '{outer:roleName}_{definition:OUTER_KEY}'
    );

    /**
     * Create all required relation columns, indexes and constraints.
     *
     * @throws ORMException
     */
    public function buildSchema()
    {
        if (empty($this->definition[ActiveRecord::PIVOT_TABLE]))
        {
            throw new ORMException(
                "Unable to build MANY_THOUGHT ({$this->recordSchema}) relation, "
                . "thought table has to be specified."
            );
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
            ActiveRecord::MANY_THOUGHT      => $this->recordSchema->getClass(),
            ActiveRecord::OUTER_KEY         => $this->definition[ActiveRecord::INNER_KEY],
            ActiveRecord::INNER_KEY         => $this->definition[ActiveRecord::OUTER_KEY],
            ActiveRecord::THOUGHT_INNER_KEY => $this->definition[ActiveRecord::THOUGHT_OUTER_KEY],
            ActiveRecord::THOUGHT_OUTER_KEY => $this->definition[ActiveRecord::THOUGHT_INNER_KEY]
        ));
    }
}