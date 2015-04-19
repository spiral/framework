<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Accessors;

use Spiral\Components\ODM\Document;
use Spiral\Components\ORM\Accessors\JsonDocument\LazyJson;
use Spiral\Components\ORM\ORMAccessor;

abstract class JsonDocument extends Document implements ORMAccessor
{
    /**
     * Serialize object data for saving into database. No getters will be applied here.
     *
     * @return mixed
     */
    public function serializeData()
    {
        return new LazyJson(parent::serializeData());
    }

    /**
     * Get array of changed or created fields for specified Entity or accessor. Following method will
     * be executed only while model updating.
     *
     * @param string $field Name of field where model/accessor stored into.
     * @return mixed
     */
    public function compileUpdates($field = '')
    {
        return $this->serializeData();
    }

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     * @return static
     */
    public function setData($data)
    {
        if (is_string($data))
        {
            $data = json_decode($data);
        }

        return parent::setData($data);
    }
}