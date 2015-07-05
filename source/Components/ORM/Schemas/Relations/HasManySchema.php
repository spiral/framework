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

class HasManySchema extends HasOneSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::HAS_MANY;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     * Has many relation allows us user custom condition and prefill filed.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = [
        ActiveRecord::INNER_KEY         => '{record:primaryKey}',
        ActiveRecord::OUTER_KEY         => '{record:roleName}_{definition:INNER_KEY}',
        ActiveRecord::CONSTRAINT        => true,
        ActiveRecord::CONSTRAINT_ACTION => 'CASCADE',
        ActiveRecord::NULLABLE          => true,
        ActiveRecord::WHERE             => [],
        ActiveRecord::PREFILL_FIELDS    => []
    ];
}