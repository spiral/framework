<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth;

use Spiral\Auth\Exception\TransportException;

/**
 * Manages list of transports by their names, manages token storage association.
 */
final class TransportRegistry
{
    /** @var HttpTransportInterface[] */
    private $transports = [];

    /** @var string */
    private $default;

    /**
     * @param string $name
     */
    public function setDefaultTransport(string $name): void
    {
        $this->default = $name;
    }

    /**
     * @param string                 $name
     * @param HttpTransportInterface $transport
     */
    public function setTransport(string $name, HttpTransportInterface $transport): void
    {
        $this->transports[$name] = $transport;
    }

    /**
     * @param string|null $name
     * @return HttpTransportInterface
     */
    public function getTransport(string $name = null): HttpTransportInterface
    {
        $name = $name ?? $this->default;

        if (!isset($this->transports[$name])) {
            throw new TransportException("Undefined auth transport {$name}");
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
