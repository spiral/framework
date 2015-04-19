<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Accessors;

use Spiral\Components\DBAL\SqlQuotable;
use Spiral\Components\ORM\ORMAccessor;
use Spiral\Support\Models\AccessorTrait;

class AtomicNumber implements ORMAccessor
{
    /**
     * Some simple methods.
     */
    use AccessorTrait;

    /**
     * Numeric value.
     *
     * @var float|int
     */
    protected $value = null;

    /**
     * Current value change.
     *
     * @var float|int
     */
    protected $delta = 0;

    /**
     * Accessors can be used to mock different model values using "representative" class, like
     * DateTime for timestamps.
     *
     * @param mixed  $data    Data to mock.
     * @param object $parent
     * @param mixed  $options Implementation specific options.
     */
    public function __construct($data = null, $parent = null, $options = null)
    {
        $this->value = $data;
        $this->parent = $parent;
    }

    /**
     * Serialize accessor mocked value. This is legacy name and used like that to be compatible with
     * ORM and ODM engines.
     *
     * @return mixed
     */
    public function serializeData()
    {
        return $this->value;
    }

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->value = $data;
        $this->delta = 0;
    }

    /**
     * Check if object has any update.
     *
     * @return bool
     */
    public function hasUpdates()
    {
        return $this->delta !== 0;
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        $this->delta = 0;
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
        if ($this->delta === 0)
        {
            return $this->value;
        }

        $sign = $this->delta > 0 ? '+' : '-';

        return new SqlQuotable("{$field} {$sign} " . abs($this->delta));
    }

    /**
     * Increment numeric value (alias for inc).
     *
     * @param float|int $delta
     * @return static
     */
    public function inc($delta)
    {
        $this->value += $delta;
        $this->delta += $delta;

        return $this;
    }

    /**
     * Increment numeric value (alias for inc).
     *
     * @param float|int $delta
     * @return static
     */
    public function add($delta)
    {
        $this->value += $delta;
        $this->delta += $delta;

        return $this;
    }

    /**
     * Decrement numeric value.
     *
     * @param float|int $delta
     * @return static
     */
    public function dec($delta)
    {
        $this->value -= $delta;
        $this->delta -= $delta;

        return $this;
    }
}