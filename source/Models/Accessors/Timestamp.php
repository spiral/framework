<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models\Accessors;

use Carbon\Carbon;
use Spiral\Support\Models\AccessorInterface;
use Spiral\Support\Models\AccessorTrait;

class Timestamp extends Carbon implements AccessorInterface
{
    /**
     * Some common methods.
     */
    use AccessorTrait;

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
        if (is_numeric($data))
        {
            parent::__construct();
            $timezone && $this->setTimezone($timezone);
            $this->setTimestamp($data);
        }
        else
        {
            parent::__construct($data, $timezone);
            is_null($data) && $this->setTimestamp(0);
        }

        $this->parent = $parent;
    }

    /**
     * Returns date formatted according to given format. Will use default format if not specified.
     *
     * @param string $format
     * @return string
     * @link http://php.net/manual/en/datetime.format.php
     */
    public function format($format = null)
    {
        return parent::format($format ?: self::DEFAULT_TO_STRING_FORMAT);
    }

    /**
     * Getting mocked value.
     *
     * @return mixed
     */
    public function serializeData()
    {
        return $this->timestamp;
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
     * (PHP 5 > 5.4.0)
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'date'     => $this->toDateTimeString(),
            'timezone' => $this->timezoneName
        ];
    }

    /**
     * Convert input variable to a valid timestamp. Can accept timestamp (will be converted to an int)
     * or valid datetime string. Timezone of input datetime string can be set.
     *
     * @param string $datetime Timestamp or valid datetime string.
     * @param string $timezone Reference timezone. Will not apply if the input is a valid timestamp.
     * @return int
     */
    public static function castTimestamp($datetime, $timezone = null)
    {
        if (!is_scalar($datetime))
        {
            return 0;
        }

        if (is_numeric($datetime))
        {
            //Nothing to do
            return (int)$datetime;
        }

        if (!empty($timezone))
        {
            return (new self($datetime, null, $timezone))->getTimestamp();
        }

        return (int)strtotime($datetime);
    }
}