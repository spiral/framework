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
}