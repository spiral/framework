<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Models\Accessors;

use Spiral\Models\AccessorInterface;

/**
 * Provides shared functionality for time based fields in both ORM and ODM components.
 *
 * You can also implement entity specific timezone using entity context.
 */
abstract class AbstractTimestamp extends \DateTime implements AccessorInterface
{
    /**
     * @var string
     */
    const DEFAULT_FORMAT = 'F d, Y H:i';

    /**
     * @var \DateTimeInterface
     */
    private $initial = false;

    /**
     * @param string $value
     * @param array  $context
     */
    public function __construct($value, array $context)
    {
        //Datetime accessor default timezone is same as currently in application
        parent::__construct(
            "@" . $this->fetchTimestamp($value)
        );

        $this->initial = clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($data)
    {
        $this->setTimestamp($this->fetchTimestamp($data));
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTimeInterface
     */
    public function packValue()
    {
        return clone $this;
    }

    /**
     * @return bool
     */
    public function hasChanges(): bool
    {
        return $this->initial->getTimestamp() != $this->getTimestamp();
    }

    /**
     * Reset state.
     */
    public function flushChanges()
    {
        $this->initial = clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->format(DATE_ISO8601);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->format(static::DEFAULT_FORMAT);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'date'     => $this->format(DATE_ISO8601),
            'timezone' => $this->getTimezone()->getName()
        ];
    }

    /**
     * Fetch timestamp from input value.
     *
     * @param mixed $value
     *
     * @return int
     */
    abstract protected function fetchTimestamp($value): int;

    /**
     * Helper method, convert input variable to a valid timestamp. Can accept timestamp (will be
     * converted to an int) or valid datetime string. Timezone of input datetime string can be set.
     *
     * @param string|int    $datetime Timestamp or valid datetime string.
     * @param \DateTimeZone $timezone Source timezone.
     *
     * @return int|null
     */
    protected function castTimestamp($datetime, \DateTimeZone $timezone = null)
    {
        if ($datetime instanceof \DateTime) {
            return $datetime->getTimestamp();
        }

        if (!is_scalar($datetime)) {
            return null;
        }

        if (is_numeric($datetime)) {
            //Nothing to do
            return (int)$datetime;
        }

        try {
            return (new \DateTime($datetime, $timezone))->getTimestamp();
        } catch (\Exception $e) {
            //Error parsing
            return null;
        }
    }
}