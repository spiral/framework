<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Events;

interface DispatcherInterface
{
    /**
     * All registered listeners will be performed in same order they were registered.
     *
     * @param string   $event    Event name.
     * @param callback $listener Valid callback or closure.
     */
    public function addListener($event, $listener);

    /**
     * Will remove known callback from specified event.
     *
     * @param string   $event    Event name.
     * @param callback $listener Valid callback or closure.
     */
    public function removeListener($event, $listener);

    /**
     * Check if specified event listened by known callback.
     *
     * @param string   $event    Event name.
     * @param callback $listener Valid callback or closure.
     * @return bool
     */
    public function hasListener($event, $listener);

    /**
     * Retrieve all event listeners.
     *
     * @param string $event Event name.
     * @return array
     */
    public function getListeners($event);

    /**
     * Fire event by name. All attached event handlers will be performed in order they were registered. Method will return
     * resulted event context which will be passed thought all event listeners.
     *
     * @param string $event   Event name.
     * @param mixed  $context Primary event content.
     * @return mixed
     */
    public function fire($event, $context = null);
}