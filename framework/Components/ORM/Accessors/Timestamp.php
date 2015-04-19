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
use Spiral\Components\ORM\ORMAccessor;
use Spiral\Support\Models\Accessors\Timestamp as BaseTimestamp;

class Timestamp extends BaseTimestamp implements ORMAccessor
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
     * @return static
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
     * @return static
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
     * Get array of changed or created fields for specified Entity or accessor. Following method will
     * be executed only while model updating.
     *
     * @param string $field Name of field where model/accessor stored into.
     * @return static
     */
    public function compileUpdates($field = '')
    {
        return $this;
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