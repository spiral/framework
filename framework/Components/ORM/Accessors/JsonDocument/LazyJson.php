<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Accessors\JsonDocument;

class LazyJson implements \JsonSerializable
{
    /**
     * Json data.
     *
     * @var mixed
     */
    protected $data = null;

    /**
     * LazyJson object provides delayed json serialization.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * String value.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->data);
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
        return $this->data;
    }
}