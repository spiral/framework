<?php

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * Dispatchers are general application flow controllers, system should start them and pass exception
 * or instance of snapshot into them when error happens.
 */
interface DispatcherInterface
{
    /**
     * Must return true if dispatcher expects to handle requests in a current environment.
     */
    public function canServe(): bool;

    /**
     * Start request execution.
     *
     * @return mixed
     */
    public function serve();
}
