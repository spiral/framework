<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Models\Accessors;

use Spiral\Models\Exceptions\AccessorException;
use Spiral\ORM\ORMInterface;
use Spiral\ORM\RecordAccessorInterface;

/**
 * Field to timestamp accessor for ORM Records. Automatically fetches database timezone and caches
 * it.
 */
class SqlTimestamp extends AbstractTimestamp implements RecordAccessorInterface
{
    /**
     * Cached list of record timezones.
     *
     * @var array
     */
    private static $recordTimezones = [];

    /**
     * @var \DateTimeZone
     */
    private $sourceTimezone;

    /**
     * @param string $value
     * @param array  $context
     */
    public function __construct($value, array $context)
    {
        $this->sourceTimezone = $this->fetchSourceTimezone($context);
        parent::__construct($value, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchTimestamp($value): int
    {
        return $this->castTimestamp($value, $this->sourceTimezone) ?? 0;
    }

    /**
     * @param string $field
     *
     * @return \DateTimeInterface
     */
    public function compileUpdates(string $field = '')
    {
        return $this->packValue();
    }

    /**
     * Must locate source timezone, but default checks cache and then performs fallback to
     * Driver based check.
     *
     * @param array $context
     *
     * @return \DateTimeZone
     */
    private function fetchSourceTimezone(array $context): \DateTimeZone
    {
        if (empty($context['entity']) || empty($context['orm'])) {
            throw new AccessorException("Invalid accessor context, expected [entity, orm]");
        }

        $class = get_class($context['entity']);

        if (!empty(self::$recordTimezones[$class])) {
            //Found in static cache
            return self::$recordTimezones[$class];
        }

        $orm = $context['orm'];
        if (!$orm instanceof ORMInterface) {
            throw new AccessorException("Invalid accessor context, ORMInterface expected");
        }

        //Timezone thought driver
        return $orm->database(
            $orm->define($class, ORMInterface::R_DATABASE)
        )->getDriver()->getTimezone();
    }
}