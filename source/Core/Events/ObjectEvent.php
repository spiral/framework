<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Events;

class ObjectEvent extends Event
{
    /**
     * Responsible object.
     *
     * @var object
     */
    public $object = null;

    /**
     * Event object created automatically via raise() method of EventDispatcher and passed to all
     * handlers listening for this event name. ObjectEvent created by event trait and keeps event
     * parent in "object" property.
     *
     * @param string $name
     * @param object $object
     * @param mixed  $context
     */
    public function __construct($name, $object, $context = null)
    {
        $this->name = $name;
        $this->object = $object;
        $this->context = $context;
    }
}