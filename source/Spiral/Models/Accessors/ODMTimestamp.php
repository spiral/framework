<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM\Accessors;

use Spiral\Components\ODM\CompositableInterface;
use Spiral\Components\ODM\Document;
use Spiral\Components\ODM\ODM;
use Spiral\Components\ODM\ODMAccessor;
use Spiral\Components\ODM\ODMException;
use Spiral\Support\Models\Accessors\AbstractTimestamp as BaseTimestamp;

class ODMTimestamp extends BaseTimestamp implements ODMAccessor
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
     * @param ODM    $odm      ODM component.
     */
    public function __construct($data = null, $parent = null, $timezone = null, ODM $odm = null)
    {
        $this->original = $data;
        if ($data instanceof \MongoDate)
        {
            parent::__construct(null, $parent);
            $this->setTimestamp($data->sec);
        }
        else
        {
            //Date not set
            parent::__construct($data, $parent);
        }
    }

    /**
     * Copy Compositable to embed into specified parent. Documents with already set parent will return
     * copy of themselves, in other scenario document will return itself. No type specified to keep
     * it compatible with AccessorInterface.
     *
     * @param CompositableInterface $parent Parent ODMCompositable object should be copied or prepared
     *                                      for.
     * @return CompositableInterface
     * @throws ODMException
     */
    public function embed($parent)
    {
        if (!$parent instanceof CompositableInterface)
        {
            throw new ODMException("Scalar arrays can be embedded only to ODM objects.");
        }

        $accessor = clone $this;
        $accessor->original = -1;
        $accessor->parent = $parent;

        return $accessor;
    }

    /**
     * Getting mocked value.
     *
     * @return \MongoDate|null
     */
    public function serializeData()
    {
        //MongoDate in a fact just a simple timestamp
        return $this->timestamp ? new \MongoDate($this->timestamp) : null;
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
     * Get generated and manually set document/object atomic updates.
     *
     * @param string $container Name of field or index where document stored into.
     * @return array
     */
    public function buildAtomics($container = '')
    {
        if (!$this->hasUpdates())
        {
            return [];
        }

        return [Document::ATOMIC_SET => [$container => $this->serializeData()]];
    }

    /**
     * Check if object has any update.
     *
     * @return bool
     */
    public function hasUpdates()
    {
        return $this->original != new \MongoDate($this->timestamp);
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        $this->original = $this->serializeData();
    }

    /**
     * Accessor default value.
     *
     * @return mixed
     */
    public function defaultValue()
    {
        return null;
    }
}