<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation;

interface ValueInterface
{
    /**
     * To validate complex object as simple value.
     *
     * @return mixed
     */
    public function serializeData();
}