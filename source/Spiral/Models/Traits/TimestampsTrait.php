<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Models\Traits;

use MongoDB\BSON\UTCDateTime;
use Spiral\Models\Events\DescribeEvent;
use Spiral\Models\Events\EntityEvent;
use Spiral\ODM\DocumentEntity;
use Spiral\ORM\Events\RecordEvent;
use Spiral\ORM\RecordEntity;
use Spiral\ORM\RecordInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Timestamps traits adds two magic fields into model/document schema time updated and time created
 * automatically populated when entity being saved. Can be used in Models and Documents.
 *
 * ORM: time_created, time_updated
 * ODM: timeCreated, timeUpdated
 */
trait TimestampsTrait
{
    /**
     * Touch object and update it's time_updated value.
     *
     * @return $this
     */
    public function touch()
    {
        if ($this instanceof RecordEntity) {
            $this->setField('time_updated', new \DateTime());
        } elseif ($this instanceof DocumentEntity) {
            $this->setField('timeUpdated', new UTCDateTime(time()));
        }

        return $this;
    }

    /**
     * Called when model class are initiated.
     */
    protected static function __init__timestamps()
    {
        /**
         * Updates values of time_updated and time_created fields.
         */
        $listener = self::__timestamps__saveListener();

        self::events()->addListener('create', $listener);
        self::events()->addListener('update', $listener);
    }

    /**
     * When schema being analyzed.
     */
    protected static function __describe__timestamps()
    {
        self::events()->addListener('describe', self::__timestamps__describeListener());
    }

    /**
     * DataEntity save.
     *
     * @return \Closure
     */
    private static function __timestamps__saveListener()
    {
        return function (EntityEvent $event, $eventName) {
            $entity = $event->getEntity();
            if ($event instanceof RecordEvent && $event->isContextual()) {
                switch ($eventName) {
                    case 'create':
                        $entity->setField('time_created', new \DateTime());
                        $event->getCommand()->addContext('time_created', new \DateTime());


                    //no-break
                    case 'update':
                        $entity->setField('time_updated', new \DateTime());
                        $event->getCommand()->addContext('time_updated', new \DateTime());
                }
            }

            if ($entity instanceof DocumentEntity) {
                switch ($eventName) {
                    case 'create':
                        $entity->setField('timeCreated', new UTCDateTime(time() * 1000));
                    //no-break
                    case 'update':
                        $entity->setField('timeUpdated', new UTCDateTime(time() * 1000));
                }
            }
        };
    }

    /**
     * Create appropriate schema modification listener. Executed only in analysis.
     *
     * @return callable
     */
    private static function __timestamps__describeListener()
    {
        return function (DescribeEvent $event) {
            if ($event->getProperty() != 'schema') {
                return;
            }

            $schema = $event->getValue();

            if ($event->getReflection()->isSubclassOf(RecordInterface::class)) {
                $schema += [
                    'time_created' => 'datetime, null',
                    'time_updated' => 'datetime, null'
                ];
            } elseif ($event->getReflection()->isSubclassOf(DocumentEntity::class)) {
                $schema += [
                    'timeCreated' => 'timestamp',
                    'timeUpdated' => 'timestamp'
                ];
            }

            //Updating schema value
            $event->setValue($schema);
        };
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    abstract public static function events(): EventDispatcherInterface;
}