<?php

declare(strict_types=1);

namespace Spiral\SendIt;

use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

interface TransportRegistryInterface
{
    /**
     * Register a custom mail transport factory.
     */
    public function registerTransport(TransportFactoryInterface $factory): void;

    /**
     * @return TransportFactoryInterface[]
     */
    public function getTransports(): array;
}
