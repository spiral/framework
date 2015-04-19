<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Support\Models\AccessorInterface;

interface ORMAccessor extends AccessorInterface
{
    /**
     * Check if object has any update.
     *
     * @return bool
     */
    public function hasUpdates();

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates();

    /**
     * Get array of changed or created fields for specified Entity or accessor. Following method will
     * be executed only while model updating.
     *
     * @param string $field Name of field where model/accessor stored into.
     * @return mixed
     */
    public function compileUpdates($field = '');
}