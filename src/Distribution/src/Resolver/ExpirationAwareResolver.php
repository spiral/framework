<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    /**
     * @var string
     */
    protected const DEFAULT_EXPIRATION_INTERVAL = 'PT60M';

    /**
     * @var \DateInterval
     */
    protected $expiration;

    /**
     * @var DateTimeIntervalFactoryInterface
     */
    protected $intervals;

    /**
     * @var DateTimeFactoryInterface
     */
    protected $dates;

    /**
     * ExpirationAwareResolver constructor.
     */
    public function __construct()
    {
        $this->dates = new DateTimeFactory();
        $this->intervals = new DateTimeIntervalFactory($this->dates);
        $this->expiration = $this->intervals->create(static::DEFAULT_EXPIRATION_INTERVAL);
    }

    /**
     * @return \DateInterval
     */
    public function getExpirationDate(): \DateInterval
    {
        return $this->expiration;
    }

    /**
     * @param DateIntervalFormat $duration
     * @return $this
     */
    public function withExpirationDate($duration): ExpirationAwareResolverInterface
    {
        $self = clone $this;
        $self->expiration = $self->intervals->create($duration);

        return $self;
    }

    /**
     * @param DateTimeFactoryInterface $factory
     * @return $this
     */
    public function withDateTimeFactory(DateTimeFactoryInterface $factory): self
    {
        $self = clone $this;
        $self->dates = $factory;

        return $self;
    }

    /**
     * @param DateTimeIntervalFactoryInterface $factory
     * @return $this
     */
    public function withDateTimeIntervalFactory(DateTimeIntervalFactoryInterface $factory): self
    {
        $self = clone $this;
        $self->intervals = $factory;

        return $self;
    }

    /**
     * @param DateIntervalFormat|null $expiration
     * @return \DateTimeInterface
     */
    protected function getExpirationDateTime($expiration): \DateTimeInterface
    {
        $expiration = $this->resolveExpirationInterval($expiration);

        return $this->intervals->toDateTime($expiration);
    }

    /**
     * @param DateIntervalFormat|null $expiration
     * @return \DateInterval
     */
    private function resolveExpirationInterval($expiration): \DateInterval
    {
        if ($expiration === null) {
            return $this->expiration;
        }

        return $this->intervals->create($expiration);
    }
}
