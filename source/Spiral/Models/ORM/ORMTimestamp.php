<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Accessors;

use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Driver;
use Spiral\Components\ORM\ORMAccessor;
use Spiral\Support\Models\Accessors\TimestampAccessor as BaseTimestamp;

class ORMTimestamp extends BaseTimestamp implements ORMAccessor
{
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
        if ($data instanceof \DateTime)
        {
            parent::__construct(null, $parent, DatabaseManager::DEFAULT_TIMEZONE);
            $this->setTimestamp($data->getTimestamp());
        }
        else
        {
            //Date not set
            parent::__construct($data, $parent, DatabaseManager::DEFAULT_TIMEZONE);
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
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->setTimestamp(self::castTimestamp($data));
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
}