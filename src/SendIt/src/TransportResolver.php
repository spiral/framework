<?php

declare(strict_types=1);

namespace Spiral\SendIt;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class TransportResolver implements TransportResolverInterface, TransportRegistryInterface
{
    /** @var TransportFactoryInterface[] */
    private array $transports = [];

    public function __construct(
        private readonly Transport $transport,
    ) {
    }

    public function registerTransport(TransportFactoryInterface $factory): void
    {
        $this->transports[] = $factory;
    }

    public function resolve(string $dsn): TransportInterface
    {
        $dsnDto = Dsn::fromString($dsn);
        foreach ($this->transports as $transport) {
            if ($transport->supports($dsnDto)) {
                return $transport->create($dsnDto);
            }
        }

        return $this->transport->fromString($dsn);
    }

    public function getTransports(): array
    {
        return $this->transports;
    }
}
