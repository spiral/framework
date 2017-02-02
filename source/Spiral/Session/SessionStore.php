<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session;

/**
 * Direct api to php session functionality. With segments support. Automatically provides access to
 * _SESSION global variable.
 *
 *
 */
class SessionStore implements SessionInterface
{
    private $started = false;

    public function start()
    {

    }
}