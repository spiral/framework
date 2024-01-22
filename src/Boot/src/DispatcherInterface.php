<?php

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * Dispatchers are general application flow controllers, system should start them and pass exception
 * or instance of snapshot into them when error happens.
 * @method static bool canServe() Must return true if the dispatcher expects to handle requests in a current environment
 */
interface DispatcherInterface
{
    /**
     * Start request execution.
     *
     * @return mixed
     */
    public function serve();
}
