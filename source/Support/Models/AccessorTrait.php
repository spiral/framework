<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models;

trait AccessorTrait
{
    /**
     * Parent DataEntity model.
     *
     * @invisible
     * @var DataEntity
     */
    protected $parent = null;

    /**
     * Embedding to another parent.
     *
     * @param object $parent
     * @return mixed
     */
    public function embed($parent)
    {
        $accessor = clone $this;
        $accessor->parent = $parent;

        return $accessor;
    }

    /**
     * Getting mocked value.
     *
     * @return mixed
     */
    abstract public function serializeData();

    /**
     * Serialize accessor mocked value. This is legacy name and used like that to be compatible with
     * ORM and ODM engines.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->serializeData();
    }

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    abstract public function setData($data);

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    public function setValue($data)
    {
        $this->setData($data);
    }

    /**
     * (PHP 5 > 5.4.0)
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->getValue();
    }

    /**
     * Simplified way to dump information.
     *
     * @return object
     */
    public function __debugInfo()
    {
        return (object)$this->jsonSerialize();
    }
}