<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session;

use Spiral\Session\Exceptions\SessionException;

/**
 * Session store interface. Every session is segment by itself.
 */
interface SessionInterface extends SegmentInterface
{
    /**
     * Get session ID or create new one if session not started.
     *
     * @return string
     * @throws SessionException
     */
    public function getID();
    
    //Access and drop segment methods
}
