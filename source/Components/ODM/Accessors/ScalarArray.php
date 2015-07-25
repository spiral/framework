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

class ScalarArray implements ODMAccessor, \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Automatically detect type based on first element.
     */
    const DETECT_TYPE = 456789;

    /**
     * Current values.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Indication that data is updated.
     *
     * @var array
     */
    protected $updated = false;

    /**
     * When solidState is true all atomic operations will be applied but not send to database, this
     * flag will be automatically set when accessor copied from one object to another.
     *
     * @var bool
     */
    protected $solidState = true;

    /**
     * Low level atomic operations.
     *
     * @var array
     */
    protected $operations = [];

    /**
     * Parent object.
     *
     * @var CompositableInterface
     */
    protected $parent = null;

    /**
     * Values types.
     *
     * @var string
     */
    protected $type = null;

    /**
     * Supported type filters.
     *
     * @var array
     */
    protected $types = [
        'int'     => 'intval',
        'float'   => 'floatval',
        'string'  => ['Spiral\Helpers\ValueHelper', 'castString'],
        'MongoId' => 'mongoID'
    ];

    /**
     * New Compositable instance. No type specified to keep it compatible with AccessorInterface.
     *
     * @param array|mixed           $data
     * @param CompositableInterface $parent
     * @param mixed                 $type Implementation specific options.
     * @param ODM                   $odm  ODM component.
     * @throws ODMException
     */
    public function __construct($data = null, $parent = null, $type = self::DETECT_TYPE, ODM $odm = null)
    {
        $this->parent = $parent;
        $this->data = is_array($data) ? $data : [];
        $this->type = $type;

        if ($this->type == self::DETECT_TYPE)
        {
            if (empty($this->data))
            {
                //How are we suppose detect something here?
                $this->type = 'string';

                return;
            }

            $this->type = null;
            switch (gettype($this->data[0]))
            {
                case 'integer':
                    $this->type = 'int';
                    break;
                case 'object':
                    if ($this->data[0] instanceof \MongoId)
                    {
                        $this->type = 'MongoId';
                    }
                    break;
                case 'double':
                case 'float':
                    $this->type = 'float';
                    break;
                case 'string':
                    $this->type = 'string';
            }
        }

        if (!isset($this->types[$this->type]))
        {
            throw new ODMException("ScalarArray can represent only scalar types and MongoId.");
        }
    }

    /**
     * When solid state is enabled no atomic operations will be pushed to databases and array will
     * be saved as one big set request.
     *
     * @param bool $solidState Solid state flag value.
     * @return $this
     */
    public function solidState($solidState)
    {
        $this->solidState = $solidState;

        return $this;
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
        $accessor->parent = $parent;
        $accessor->solidState = $accessor->updated = true;

        return $accessor;
    }

    /**
     * Serialize object data for saving into database. This is common method for documents and compositors.
     *
     * @return mixed
     */
    public function serializeData()
    {
        return $this->data;
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

        if (!$this->hasUpdates())
        {
            return [];
        }

        if ($this->solidState)
        {
            return [Document::ATOMIC_SET => [$container => $this->serializeData()]];
        }

        $atomics = [];
        foreach ($this->operations as $operation => $value)
        {
            $atomics = ['$' . $operation => [$container => $value]];
        }

        return $atomics;
    }

    /**
     * Check if object has any update.
     *
     * @return bool
     */
    public function hasUpdates()
    {
        return $this->updated || $this->operations;
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        $this->updated = false;
        $this->operations = [];
    }

    /**
     * Convert value to expected format.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function filterValue($value)
    {
        if (!is_scalar($value))
        {
            return null;
        }

        try
        {
            $value = call_user_func($this->types[$this->type], $value);
        }
        catch (\Exception $exception)
        {
            return null;
        }

        return $value;
    }

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->updated = $this->solidState = true;

        if (!is_array($data))
        {
            //Ignoring
            return;
        }

        $this->data = [];
        foreach ($data as $value)
        {
            if (($value = $this->filterValue($value)) !== null)
            {
                $this->data[] = $value;
            }
        }
    }

    /**
     * Clearing all values.
     *
     * @return $this
     */
    public function clear()
    {
        $this->solidState = $this->updated = true;
        $this->data = [];

        return $this;
    }

    /**
     * Accessor default value.
     *
     * @return mixed
     */
    public function defaultValue()
    {
        /**
         * Expected to be initiated with default value.
         */
        return $this->data;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return Document
     * @throws ODMException
     */
    public function offsetGet($offset)
    {
        if (!isset($this->data[$offset]))
        {
            throw new ODMException("Undefined offset '{$offset}'.");
        }

        return $this->data[$offset];
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     * @return void
     * @throws ODMException
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->solidState)
        {
            throw new ODMException(
                "Direct offset operation can not be performed for ScalarArray in non solid state."
            );
        }

        $this->updated = true;
        if (is_null($offset))
        {
            $this->data[] = $value;
        }
        else
        {
            $this->data[$offset] = $value;
        }
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @return void
     * @throws ODMException
     */
    public function offsetUnset($offset)
    {
        if (!$this->solidState)
        {
            throw new ODMException(
                "Direct offset operation can not be performed for ScalarArray in non solid state."
            );
        }

        $this->updated = true;
        unset($this->data[$offset]);
    }

    /**
     * Alias for atomic operation $push.
     *
     * @param string $value
     * @return $this
     */
    public function push($value)
    {
        if (($value = $this->filterValue($value)) === null)
        {
            return $this;
        }

        array_push($this->data, $value);
        $this->operations['push']['$each'][] = $value;

        return $this;
    }

    /**
     * Alias for atomic operation $addToSet.
     *
     * @param string $value
     * @return $this
     */
    public function addToSet($value)
    {
        if (($value = $this->filterValue($value)) === null)
        {
            return $this;
        }

        !in_array($value, $this->data) && array_push($this->data, $value);
        $this->operations['addToSet']['$each'] = $value;

        return $this;
    }

    /**
     * Alias for atomic operation $pull.
     *
     * @param string $value
     * @return $this
     */
    public function pull($value)
    {
        if (($value = $this->filterValue($value)) === null)
        {
            return $this;
        }

        $this->data = array_filter($this->data, function ($item) use ($value)
        {
            return $item != $value;
        });

        $this->operations['pull'] = $value;

        return $this;
    }

    /**
     * Count of documents nested in compositor.
     *
     * @return int|void
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Retrieve an external iterator with all nested documents (in object form).
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return mixed
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * (PHP 5 >= 5.4.0)
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed
     */
    function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * Simplified way to dump information.
     *
     * @return Object
     */
    public function __debugInfo()
    {
        return (object)[
            'data'    => $this->serializeData(),
            'type'    => $this->type,
            'atomics' => $this->buildAtomics('scalarArray')

        ];
    }
}