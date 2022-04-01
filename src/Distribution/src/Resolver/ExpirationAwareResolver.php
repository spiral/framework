<?php

declare(strict_types=1);

namespace Spiral\Distribution\Resolver;

use Spiral\Distribution\ExpirationAwareResolverInterface;
use Spiral\Distribution\Internal\DateTimeFactory;
use Spiral\Distribution\Internal\DateTimeFactoryInterface;
use Spiral\Distribution\Internal\DateTimeIntervalFactory;
use Spiral\Distribution\Internal\DateTimeIntervalFactoryInterface;

/**
 * @psalm-import-type DateIntervalFormat from DateTimeIntervalFactoryInterface
 */
abstract class ExpirationAwareResolver extends UriResolver implements ExpirationAwareResolverInterface
{
    protected const DEFAULT_EXPIRATION_INTERVAL = 'PT60M';

    protected \DateInterval $expiration;
    protected DateTimeIntervalFactoryInterface $intervals;
    protected DateTimeFactoryInterface $dates;

    /**
     * ExpirationAwareResolver constructor.
     */
    public function __construct()
    {
        $this->dates = new DateTimeFactory();
        $this->intervals = new DateTimeIntervalFactory($this->dates);
        $this->expiration = $this->intervals->create(static::DEFAULT_EXPIRATION_INTERVAL);
    }

    public function getExpirationDate(): \DateInterval
    {
        return $this->expiration;
    }

    /**
     * @return $this
     */
    public function withExpirationDate(mixed $duration): ExpirationAwareResolverInterface
    {
        $self = clone $this;
        $self->expiration = $self->intervals->create($duration);

        return $self;
    }

    public function withDateTimeFactory(DateTimeFactoryInterface $factory): self
    {
        $self = clone $this;
        $self->dates = $factory;

        return $self;
    }

    public function withDateTimeIntervalFactory(DateTimeIntervalFactoryInterface $factory): self
    {
        $self = clone $this;
        $self->intervals = $factory;

        return $self;
    }

    protected function getExpirationDateTime(mixed $expiration): \DateTimeInterface
    {
        $expiration = $this->resolveExpirationInterval($expiration);

        return $this->intervals->toDateTime($expiration);
    }

    private function resolveExpirationInterval(mixed $expiration): \DateInterval
    {
        if ($expiration === null) {
            return $this->expiration;
        }

        return $this->intervals->create($expiration);
    }
}
