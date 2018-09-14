<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

/**
 * Dispatchers are general application flow controllers, system should start them and pass exception
 * or instance of snapshot into them when error happens.
 */
interface DispatcherInterface
{
    /**
     * Must return true if dispatcher expects to handle requests in a current environment.
     *
     * @return bool
     */
    public function enabled(): bool;

    /**
     * Start request execution.
     */
    public function start();
}