<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM\Traits;

use Spiral\Components\ODM\Document;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Events\Event;
use Spiral\Core\Events\ObjectEvent;

trait TimestampsTrait
{

    /**
     * EventDispatcher instance which is currently attached to component implementation, can be redefined
     * using setDispatcher() method. EventDispatcher instance will be created on demand and depends on
     * "events" binding in spiral core. Every new EventDispatcher will receive "name" argument which
     * is equal to getAlias() method result and declares events namespace.
     *
     * If no "events" binding presented, default dispatcher will be used (performance reasons).
     *
     * @return DispatcherInterface
     */
    abstract public static function dispatcher();

    /**
     * Init timestamps.
     *
     * @param mixed $options Custom options.
     */
    protected static function initTimestamps($options = null)
    {
        if ($options == Document::SCHEMA_ANALYSIS)
        {
            $listener = function (Event $event)
            {
                if ($event->context['property'] == 'schema')
                {
                    $event->context['value']['timeCreated'] = 'timestamp';
                    $event->context['value']['timeUpdated'] = 'timestamp';
                }
            };

            //This check is required as Document::SCHEMA_ANALYSIS will be provided multiple times
            if (!self::dispatcher()->hasListener('describe', $listener))
            {
                self::dispatcher()->addListener('describe', $listener);
            }
        }

        self::dispatcher()->addListener('saving', array(__CLASS__, 'timestampsHandler'));
        self::dispatcher()->addListener('updating', array(__CLASS__, 'timestampsHandler'));
    }

    /**
     * Timestamp updates.
     *
     * @param ObjectEvent $event
     */
    public static function timestampsHandler(ObjectEvent $event)
    {
        switch ($event->getName())
        {
            case 'saving':
                $event->object->setField('timeCreated', new \MongoDate(time()), false);
            case 'updating':
                $event->object->setField('timeUpdated', new \MongoDate(time()), false);
        }
    }
}