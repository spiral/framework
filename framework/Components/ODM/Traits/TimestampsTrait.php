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
use Spiral\Core\Events\Event;
use Spiral\Core\Events\ObjectEvent;

trait TimestampsTrait
{
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

            if (!static::dispatcher()->hasListener('describe', $listener))
            {
                static::dispatcher()->addListener('describe', $listener);
            }
        }

        static::dispatcher()->addListener('saving', array(__CLASS__, 'timestampsHandler'));
        static::dispatcher()->addListener('updating', array(__CLASS__, 'timestampsHandler'));
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