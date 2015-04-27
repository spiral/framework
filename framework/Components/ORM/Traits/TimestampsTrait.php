<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Traits;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\Driver;
use Spiral\Components\ORM\Entity;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Events\Event;
use Spiral\Core\Events\ObjectEvent;

/**
 * @method DispatcherInterface dispatcher()
 */
trait TimestampsTrait
{
    /**
     * Init timestamps.
     *
     * @param mixed $options Custom options.
     */
    protected static function initTimestamps($options = null)
    {
        if ($options == Entity::SCHEMA_ANALYSIS)
        {
            $listener = function (Event $event)
            {
                if ($event->context['property'] == 'schema')
                {
                    $event->context['value']['time_created'] = 'timestamp,null';
                    $event->context['value']['time_updated'] = 'timestamp,null';
                }

                if ($event->context['property'] == 'secured')
                {
                    //Not editable by user via mass assignment
                    $event->context['value'][] = 'time_created';
                    $event->context['value'][] = 'time_updated';
                }
            };

            //This check is required as Entity::SCHEMA_ANALYSIS will be provided multiple times
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
                $event->object->setField('time_created', new \DateTime(), false);
            case 'updating':
                $event->object->setField('time_updated', new \DateTime(), false);
        }
    }
}