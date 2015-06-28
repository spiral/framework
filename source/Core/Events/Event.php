<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Events;

class Event
{
    /**
     * Event name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Event context data or object, can be modified while performing.
     *
     * @var null
     */
    public $context = null;

    /**
     * Name of listeners event were passed thought.
     *
     * @var array
     */
    public $passedThough = array();

    /**
     * Indication that event chain were stopped by one of handlers.
     *
     * @var bool
     */
    protected $stopped = false;

    /**
     * Event object created automatically via raise() method of EventDispatcher and passed to all
     * handlers listening for this event name.
     *
     * @param string $name
     * @param mixed  $context
     */
    public function __construct($name, $context = null)
    {
        $this->name = $name;
        $this->context = $context;
    }

    /**
     * Event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Indication that event chain were stopped by one of handlers.
     *
     * @return bool
     */
    public function isStopped()
    {
        return $this->stopped;
    }

    /**
     * Stops event chain, EventDispatcher will end performing right after listener called this method.
     */
    public function stopPropagation()
    {
        $this->stopped = true;
    }

    /**
     * Destructing event to clean context and options.
     */
    public function __destruct()
    {
        $this->context = null;
    }
}