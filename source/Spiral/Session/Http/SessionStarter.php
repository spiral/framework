<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session\Http;

use Spiral\Http\MiddlewareInterface;

/**
 * HttpMiddleware used to create and commit session data using cookies as sessionID provider.
 *
 * ATTENTION, YOU CAN NOT USE THIS MIDDLEWARE IN NESTED REQUESTS, DEFAULT PHP SESSION MECHANISM IS
 * USED.
 */
class SessionStarter implements MiddlewareInterface
{

}