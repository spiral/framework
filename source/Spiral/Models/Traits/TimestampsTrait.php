<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Traits;

use Spiral\Events\DispatcherInterface;
use Spiral\Events\Event;
use Spiral\Events\EventsException;
use Spiral\Models\DataEntity;
use Spiral\Events\ObjectEvent;
use Spiral\Models\Schemas\EntitySchema;
use Spiral\ODM\Document;
use Spiral\ODM\Schemas\DocumentSchema;
use Spiral\ORM\ActiveRecord;
use Spiral\ORM\Schemas\ModelSchema;

/**
 * @method DispatcherInterface dispatcher()
 */
trait TimestampsTrait
{
    /**
     * EventDispatcher instance which is currently attached to component implementation, can be redefined
     * using setDispatcher() method. Has to be defined statically.
     *
     * @return DispatcherInterface
     * @throws EventsException
     */
    abstract public function events();

    /**
     * Init timestamps.
     *
     * @param mixed $options Custom options.
     */
    protected static function initTimestamps($options = null)
    {
        $dispatcher = self::dispatcher();

        if ($options == DataEntity::SCHEMA_ANALYSIS)
        {
            //Schema analysis is requested
            $listener = self::schemaListener();

            if (!$dispatcher->hasListener('describe', $listener))
            {
                $dispatcher->addListener('describe', $listener);
            }
        }

        $dispatcher->addListener('saving', [static::class, 'timestampsHandler']);
        $dispatcher->addListener('updating', [static::class, 'timestampsHandler']);
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
        if ($model instanceof ActiveRecord)
        {
            switch ($event->getName())
            {
                case 'saving':
                    $model->setField('time_created', new \DateTime(), false);
                case 'updating':
                    $model->setField('time_updated', new \DateTime(), false);
            }
        }
        elseif ($model instanceof Document)
        {
            switch ($event->getName())
            {
                case 'saving':
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
        return function (Event $event)
        {
            /**
             * @var EntitySchema $schema
             */
            $schema = $event->context()['schema'];

            if ($event->context()['property'] == 'secured')
            {
                //Forbidding mass assignments
                switch ($schema->getClass())
                {
                    case ModelSchema::class:
                        $event->context()['value'][] = 'time_created';
                        $event->context()['value'][] = 'time_updated';
                        break;
                    case DocumentSchema::class:
                        $event->context()['value'][] = 'timeCreated';
                        $event->context()['value'][] = 'timeUpdated';
                        break;
                }
            }
            elseif ($event->context()['property'] == 'schema')
            {
                //Registering fields in schema
                switch ($schema->getClass())
                {
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