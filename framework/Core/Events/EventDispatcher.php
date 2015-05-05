<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Events;

use Spiral\Core\Component;
use Spiral\Core\Container;

class EventDispatcher extends Component implements DispatcherInterface
{
    /**
     * All created or registered event dispatchers.
     *
     * @var array
     */
    protected static $dispatchers = array();

    /**
     * Event listeners, use addHandler, removeHandler for adding new handlers and raiseEvent to
     * perform specific events.
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * All registered listeners will be performed in same order they were registered.
     *
     * @param string   $event    Event name.
     * @param callback $listener Valid callback or closure.
     * @return static
     */
    public function addListener($event, $listener)
    {
        if (!isset($this->listeners[$event]))
        {
            $this->listeners[$event] = array();
        }

        $this->listeners[$event][] = $listener;

        return $this;
    }

    /**
     * Will remove known callback from specified event.
     *
     * @param string   $event    Event name.
     * @param callback $listener Valid callback or closure.
     * @return static
     */
    public function removeListener($event, $listener)
    {
        if ($this->hasListener($event, $listener))
        {
            unset($this->listeners[$event][array_search($listener, $this->listeners[$event])]);
        }

        return $this;
    }

    /**
     * Check if specified event listened by known callback.
     *
     * @param string   $event    Event name.
     * @param callback $listener Valid callback or closure.
     * @return bool
     */
    public function hasListener($event, $listener)
    {
        if (isset($this->listeners[$event]))
        {
            return in_array($listener, $this->listeners[$event]);
        }

        return false;
    }

    /**
     * Retrieve all event listeners.
     *
     * @param string $event Event name.
     * @return array
     */
    public function getListeners($event)
    {
        if (array_key_exists($event, $this->listeners))
        {
            return $this->listeners[$event];
        }

        return array();
    }

    /**
     * Fire event by name. All attached event handlers will be performed in order they were registered.
     * Method will return resulted event context which will be passed thought all event listeners.
     *
     * @param string $event   Event name.
     * @param mixed  $context Primary event content.
     * @return mixed
     */
    public function fire($event, $context = null)
    {
        if (is_object($event))
        {
            $walker = $event;
            $event = $event->getName();
        }

        if (empty($this->listeners[$event]))
        {
            return isset($walker) ? $walker->context : $context;
        }

        if (!isset($walker))
        {
            $walker = new Event($event, $context);
        }

        foreach ($this->listeners[$event] as $listener)
        {
            call_user_func($listener, $walker);
            $walker->passedThough[] = $listener;
            if ($walker->isStopped())
            {
                break;
            }
        }

        return $walker->context;
    }

    /**
     * Pre-define event dispatcher for specified component.
     *
     * @param string              $name Component alias.
     * @param DispatcherInterface $dispatcher
     */
    public static function setDispatcher($name, DispatcherInterface $dispatcher)
    {
        self::$dispatchers[$name] = $dispatcher;
    }

    /**
     * Check is specified event dispatcher already exists.
     *
     * @param string $name
     * @return bool
     */
    public static function hasDispatcher($name)
    {
        return isset(self::$dispatchers[$name]);
    }

    /**
     * Get instance of event dispatcher for specified component. By default instance of EventDispatcher
     * will be created, this behaviour can be redefined via binding "events" in Container.
     *
     * @param string $name Component alias.
     * @return mixed|null|object|static
     */
    public static function getDispatcher($name)
    {
        if (isset(self::$dispatchers[$name]))
        {
            return self::$dispatchers[$name];
        }

        $container = Container::getInstance();
        if (!$container->hasBinding('events'))
        {
            return self::$dispatchers[$name] = new static();
        }

        return self::$dispatchers[$name] = $container->get('events');
    }
}