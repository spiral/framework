<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Models\Accessors;

use MongoDB\BSON\UTCDateTime;
use Spiral\ODM\CompositableInterface;

/**
 * Timezone is fixed for mongodb. Packs into MongoDate
 */
class UTCMongoTimestamp extends AbstractTimestamp implements CompositableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function fetchTimestamp($value): int
    {
        if ($value instanceof UTCDateTime) {
            $value = $value->toDateTime();
        }

        return $this->castTimestamp($value, new \DateTimeZone('UTC')) ?? 0;
    }

    /**
     * @return \MongoDB\BSON\UTCDateTime
     */
    public function packValue()
    {
        return new UTCDateTime($this);
    }

    /**
     * {@inheritdoc}
     */
    public function buildAtomics(string $container = ''): array
    {
        return ['$set' => [$container => $this->packValue()]];
    }
}