<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Models\Traits;

use Spiral\Models\DataEntity;
use Spiral\Models\Events\DescribeEvent;
use Spiral\Models\Events\EntityEvent;
use Spiral\ODM\Document;
use Spiral\ODM\Entities\Schemas\DocumentSchema;
use Spiral\ORM\Entities\Schemas\RecordSchema;
use Spiral\ORM\Record;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Timestamps traits adds two magic fields into model/document schema time updated and time created
 * automatically populated when entity being saved. Can be used in Models and Documents.
 *
 * ORM: time_created, time_updated
 * ODM: timeCreated, timeUpdated
 * 
 * @todo find alternatives to declarative traits
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
        if ($this instanceof Record) {
            $this->setField('time_updated', new \DateTime(), false);
        } elseif ($this instanceof Document) {
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
         * @var EventDispatcher $dispatcher
         */
        $dispatcher = self::events();

        /**
         * Updates values of time_updated and time_created fields.
         */
        $listener = self::__timestamps__saveListener();

        $dispatcher->addListener('saving', $listener);
        $dispatcher->addListener('updating', $listener);
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
            /**
             * @var DataEntity $model
             */
            $model = $event->entity();
            if ($model instanceof Record) {
                switch ($eventName) {
                    case 'saving':
                        $model->setField('time_created', new \DateTime(), false);
                    //no-break
                    case 'updating':
                        $model->setField('time_updated', new \DateTime(), false);
                }
            }

            if ($model instanceof Document) {
                switch ($eventName) {
                    case 'saving':
                        $model->setField('timeCreated', new \MongoDate(time()), false);
                    //no-break
                    case 'updating':
                        $model->setField('timeUpdated', new \MongoDate(time()), false);
                }
            }
        };
    }

    /**
     * Create appropriate schema modification listener.
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
            switch (get_class($event->reflection())) {
                case RecordSchema::class:
                    $schema['time_created'] = 'datetime,null';
                    $schema['time_updated'] = 'datetime,null';
                    break;
                case DocumentSchema::class:
                    $schema['timeCreated'] = 'timestamp';
                    $schema['timeUpdated'] = 'timestamp';
                    break;
            }

            //Updating schema value
            $event->setValue($schema);
        };
    }
}
