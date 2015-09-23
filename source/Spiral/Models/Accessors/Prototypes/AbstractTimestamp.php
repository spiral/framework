<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Models\Accessors\Prototypes;

use Carbon\Carbon;

/**
 * Abstract implementation of timestamp accessor using Carbon as base.
 */
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
     * {@inheritdoc}
     */
    public function setValue($data)
    {
        $this->setTimestamp($this->castTimestamp($data));
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toIso8601String();
    }

    /**
     * @return object
     */
    public function __debugInfo()
    {
        return (object)[
            'date'     => $this->toDateTimeString(),
            'timezone' => $this->timezoneName
        ];
    }

    /**
     * Convert input variable to a valid timestamp. Can accept timestamp (will be converted to an
     * int) or valid datetime string. Timezone of input datetime string can be set.
     *
     * @param string $datetime Timestamp or valid datetime string.
     * @param string $timezone Reference timezone. Will not apply if the input is a valid
     *                         timestamp.
     * @return int
     */
    protected function castTimestamp($datetime, $timezone = null)
    {
        if (!is_scalar($datetime)) {
            return 0;
        }

        if (is_numeric($datetime)) {
            //Nothing to do
            return (int)$datetime;
        }

        if (!empty($timezone)) {
            return (new Carbon($datetime, null, $timezone))->getTimestamp();
        }

        return (int)strtotime($datetime);
    }
}