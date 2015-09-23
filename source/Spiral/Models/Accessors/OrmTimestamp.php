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
use Spiral\Database\Entities\Driver;
use Spiral\Models\Accessors\Prototypes\AbstractTimestamp;
use Spiral\Models\EntityInterface;
use Spiral\ORM\ActiveAccessorInterface;
use Spiral\ORM\Record;

/**
 * ORM record accessor used to mock database timestamps and date field using Carbon class. Field
 * timezone automatically resolved using default database timezone specified in database provider.
 */
class OrmTimestamp extends AbstractTimestamp implements ActiveAccessorInterface
{
    /**
     * @invisible
     * @var Record
     */
    protected $parent = null;

    /**
     * Original value.
     *
     * @var mixed
     */
    protected $original = null;

    /**
     * {@inheritdoc}
     */
    public function __construct($data = null, EntityInterface $parent = null)
    {
        $this->parent = $parent;
        if ($data instanceof \DateTime) {
            parent::__construct(null, DatabaseManager::DEFAULT_TIMEZONE);
            $this->setTimestamp($data->getTimestamp());
        } else {
            parent::__construct($data, DatabaseManager::DEFAULT_TIMEZONE);
        }

        if ($this->getTimestamp() === false) {
            //Correcting default values
            $this->setTimestamp(0);
        }

        $this->original = $this->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function embed(EntityInterface $parent)
    {
        $accessor = clone $this;
        $accessor->original = -1;
        $accessor->parent = $parent;

        return $accessor;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeData()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultValue(Driver $driver)
    {
        return $driver::DEFAULT_DATETIME;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUpdates()
    {
        return $this->original != $this->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function flushUpdates()
    {
        $this->original = $this->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function compileUpdates($field = '')
    {
        return $this;
    }
}