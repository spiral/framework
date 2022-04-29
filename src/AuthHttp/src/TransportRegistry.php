<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Spiral\Auth\Exception\TransportException;

/**
 * Manages list of transports by their names, manages token storage association.
 */
final class TransportRegistry
{
    /** @var HttpTransportInterface[] */
    private array $transports = [];
    private ?string $default = null;

    public function setDefaultTransport(string $name): void
    {
        $this->default = $name;
    }

    public function setTransport(string $name, HttpTransportInterface $transport): void
    {
        $this->transports[$name] = $transport;
    }

    public function getTransport(string $name = null): HttpTransportInterface
    {
        $name ??= $this->default;

        if (!isset($this->transports[$name])) {
            throw new TransportException(\sprintf('Undefined auth transport %s', $name));
        }

        return $this->transports[$name];
    }

    /**
     * @return HttpTransportInterface[]
     */
    public function getTransports(): array
    {
        return $this->transports;
    }
}
