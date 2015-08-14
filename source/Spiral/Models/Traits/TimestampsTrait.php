<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Models\Traits;

use Spiral\Events\DispatcherInterface;
use Spiral\Events\Entities\Event;
use Spiral\Events\Entities\ObjectEvent;
use Spiral\Models\DataEntity;
use Spiral\Models\Reflections\ReflectionEntity;
use Spiral\ODM\Document;
use Spiral\ODM\Entities\Schemas\DocumentSchema;
use Spiral\ORM\Entities\Schemas\ModelSchema;
use Spiral\ORM\Model;

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
     * Must be declared statically and provide event dispatcher.
     *
     * @return DispatcherInterface
     */
    abstract public function events();

    /**
     * Init timestamps.
     *
     * @param bool $analysis DataEntity is being analyzed.
     */
    protected static function initTimestamps($analysis)
    {
        $dispatcher = self::events();

        if ($analysis) {
            //To modify database
            $listener = self::schemaListener();
            if (!$dispatcher->hasListener('describe', $listener)) {
                $dispatcher->listen('describe', $listener);
            }

            return;
        }

        $dispatcher->listen('saving', [static::class, 'timestampsHandler']);
        $dispatcher->listen('updating', [static::class, 'timestampsHandler']);
    }

    /**
     * Timestamp updates.
     *
     * @param ObjectEvent $event
     */
    public static function timestampsHandler(ObjectEvent $event)
    {
        /**
         * @var DataEntity $model
         */
        $model = $event->parent();
        if ($model instanceof Model) {
            switch ($event->name()) {
                case 'saving':
                    //There is no break statement missing
                    $model->setField('time_created', new \DateTime(), false);
                case 'updating':
                    $model->setField('time_updated', new \DateTime(), false);
            }
        }

        if ($model instanceof Document) {
            switch ($event->name()) {
                case 'saving':
                    //There is no break statement missing
                    $model->setField('timeCreated', new \MongoDate(time()), false);
                case 'updating':
                    $model->setField('timeUpdated', new \MongoDate(time()), false);
            }
        }
    }

    /**
     * Create appropriate schema modification listener.
     *
     * @return callable
     */
    private static function schemaListener()
    {
        return function (Event $event) {
            /**
             * @var ReflectionEntity $schema
             */
            $schema = $event->context()['schema'];

            if ($event->context()['property'] == 'secured' && is_array($event->context()['value'])) {
                //Forbidding mass assignments
                switch (get_class($schema)) {
                    case ModelSchema::class:
                        $event->context()['value'][] = 'time_created';
                        $event->context()['value'][] = 'time_updated';
                        break;
                    case DocumentSchema::class:
                        $event->context()['value'][] = 'timeCreated';
                        $event->context()['value'][] = 'timeUpdated';
                        break;
                }
            } elseif ($event->context()['property'] == 'schema') {
                //Registering fields in schema
                switch (get_class($schema)) {
                    case ModelSchema::class:
                        $event->context()['value']['time_created'] = 'timestamp,null';
                        $event->context()['value']['time_updated'] = 'timestamp,null';
                        break;
                    case DocumentSchema::class:
                        $event->context()['value']['timeCreated'] = 'timestamp';
                        $event->context()['value']['timeUpdated'] = 'timestamp';
                        break;
                }
            }
        };
    }
}