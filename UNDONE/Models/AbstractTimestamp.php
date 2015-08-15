<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Models\Accessors;

use Carbon\Carbon;

abstract class AbstractTimestamp extends Carbon
{
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
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->setTimestamp($this->castTimestamp($data));
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
    protected function castTimestamp($datetime, $timezone = null)
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
            return (new Carbon($datetime, null, $timezone))->getTimestamp();
        }

        return (int)strtotime($datetime);
    }

    /**
     * Simplified form for debugging.
     *
     * @return mixed
     */
    public function __debugInfo()
    {
        return (object)$this->jsonSerialize();
    }
}