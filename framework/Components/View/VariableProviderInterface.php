<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View;

use Spiral\Core\Events\Event;

interface VariableProviderInterface
{
    /**
     * Called by event component at time of composing view static variables. Such variables will
     * change cache file name. Class which implements this method should add new variable to event
     * context.
     *
     * @param Event $event
     */
    public function viewVariables(Event $event);
}