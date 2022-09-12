<?php

declare(strict_types=1);

namespace Spiral\Boot;

use Closure;

interface BootloadManagerInterface
{
    /**
     * Get bootloaded classes.
     */
    public function getClasses(): array;

    /**
     * Bootload set of classes. Support short and extended syntax with
     * bootload options (to be passed into boot method).
     *
     * [
     *    SimpleBootloader::class,
     *    CustomizedBootloader::class => ["option" => "value"]
     * ]
     *
     * @param array<class-string>|array<class-string,array<string,mixed>> $classes
     * @param array<Closure> $bootingCallbacks
     * @param array<Closure> $bootedCallbacks
     *
     * @throws \Throwable
     */
    public function bootload(array $classes, array $bootingCallbacks = [], array $bootedCallbacks = []): void;
}
