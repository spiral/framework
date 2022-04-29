<?php

declare(strict_types=1);

namespace Spiral\Boot;

use Spiral\Boot\Exception\BootException;

interface KernelInterface
{
    /**
     * Add new dispatcher. This method must only be called before method `serve`
     * will be invoked.
     */
    public function addDispatcher(DispatcherInterface $dispatcher): self;

    /**
     * Start application and serve user requests using selected dispatcher or throw
     * an exception.
     *
     * @throws BootException
     * @throws \Throwable
     */
    public function serve(): mixed;
}
