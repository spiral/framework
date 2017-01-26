<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Models\Traits;

use Spiral\Models\Events\DescribeEvent;
use Spiral\Models\Events\EntityEvent;
use Spiral\ODM\DocumentEntity;
use Spiral\ODM\Schemas\DocumentSchema;
use Spiral\ORM\RecordEntity;
use Spiral\ORM\Schemas\RecordSchema;
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
            $this->setField('time_updated', new \DateTime(), false);
        } elseif ($this instanceof DocumentEntity) {
            $this->setField('timeUpdated', new \MongoDate(time()), false);
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

        self::events()->addListener('saving', $listener);
        self::events()->addListener('updating', $listener);
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
            if ($entity instanceof RecordEntity) {
                switch ($eventName) {
                    case 'create':
                        $entity->setField('time_created', new \DateTime(), false);
                    //no-break
                    case 'update':
                        $entity->setField('time_updated', new \DateTime(), false);
                }
            }

            if ($entity instanceof DocumentEntity) {
                switch ($eventName) {
                    case 'create':
                        $entity->setField('timeCreated', new \MongoDate(time()), false);
                    //no-break
                    case 'update':
                        $entity->setField('timeUpdated', new \MongoDate(time()), false);
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

            //Registering fields in schema
            switch (get_class($event->getReflection())) {
                case RecordSchema::class:
                    $schema += [
                        'time_created' => 'datetime, null',
                        'time_updated' => 'datetime, null'
                    ];
                    break;
                case DocumentSchema::class:
                    $schema += [
                        'timeCreated' => 'timestamp',
                        'timeUpdated' => 'timestamp'
                    ];
                    break;
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