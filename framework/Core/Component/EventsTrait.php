<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Component;

use Spiral\Core\Container;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Events\EventDispatcher;
use Spiral\Core\Events\ObjectEvent;

trait EventsTrait
{
    /**
     * Sets event dispatcher. Event dispatcher will be associated with specific component by it's alias.
     *
     * @param DispatcherInterface $dispatcher
     */
    public static function setDispatcher(DispatcherInterface $dispatcher = null)
    {
        EventDispatcher::setDispatcher(static::getAlias(), $dispatcher);
    }

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
    public static function dispatcher()
    {
        return EventDispatcher::getDispatcher(static::getAlias());
    }

    /**
     * Fire object associated event. Object instance will always be passed in context key "parent".
     *
     * @param string $event
     * @param mixed  $context Event context.
     * @return mixed
     */
    protected function event($event, $context = null)
    {
        if (!EventDispatcher::hasDispatcher(static::getAlias()))
        {
            return $context;
        }

        return self::dispatcher()->fire(new ObjectEvent($event, $this, $context));
    }

    /**
     * Add event listener to specified event.
     *
     * @param string   $event    Event name.
     * @param callable $listener Callback.
     */
    public function on($event, $listener)
    {
        self::dispatcher()->addListener($event, $listener);
    }
}