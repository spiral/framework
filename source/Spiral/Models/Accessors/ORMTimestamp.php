<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Models\Accessors;

use Spiral\Database\DatabaseManager;
use Spiral\Database\Driver;
use Spiral\ORM\ActiveRecord;
use Spiral\ORM\ORMAccessor;

class ORMTimestamp extends AbstractTimestamp implements ORMAccessor
{
    /**
     * Parent models.
     *
     * @var ActiveRecord
     */
    protected $parent = null;

    /**
     * Original value.
     *
     * @var mixed
     */
    protected $original = null;

    /**
     * Accessors can be used to mock different model values using "representative" class, like
     * DateTime for timestamps.
     *
     * @param mixed  $data
     * @param object $parent
     * @param mixed  $timezone Source date timezone.
     */
    public function __construct($data = null, $parent = null, $timezone = null)
    {
        $this->parent = $parent;
        if ($data instanceof \DateTime)
        {
            parent::__construct(null, DatabaseManager::DEFAULT_TIMEZONE);
            $this->setTimestamp($data->getTimestamp());
        }
        else
        {
            parent::__construct($data, DatabaseManager::DEFAULT_TIMEZONE);
        }

        if ($this->getTimestamp() === false)
        {
            //Correcting default values
            $this->setTimestamp(0);
        }

        $this->original = $this->getTimestamp();
    }

    /**
     * Embed to another parent.
     *
     * @param object $parent
     * @return $this
     */
    public function embed($parent)
    {
        $accessor = clone $this;
        $accessor->original = -1;
        $accessor->parent = $parent;

        return $accessor;
    }

    /**
     * Getting mocked value.
     *
     * @return $this
     */
    public function serializeData()
    {
        return $this;
    }

    /**
     * Accessor default value specific to driver.
     *
     * @param Driver $driver
     * @return mixed
     */
    public function defaultValue(Driver $driver)
    {
        return $driver::DEFAULT_DATETIME;
    }

    /**
     * Check if object has any update.
     *
     * @return bool
     */
    public function hasUpdates()
    {
        return $this->original != $this->getTimestamp();
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        $this->original = $this->getTimestamp();
    }


    /**
     * Get new field value to be send to database.
     *
     * @param string $field Name of field where model/accessor stored into.
     * @return $this
     */
    public function compileUpdates($field = '')
    {
        return $this;
    }
}