<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Distribution\Internal;

/**
 * @internal DateTimeIntervalFactory is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Distribution
 */
final class DateTimeIntervalFactory implements DateTimeIntervalFactoryInterface
{
    /**
     * @var string
     */
    private const ERROR_INVALID_INTERVAL_TYPE = 'The value of type `%s` is not a valid date interval type';
    /**
     * @var DateTimeFactoryInterface|null
     */
    private $factory;

    /**
     * @param DateTimeFactoryInterface|null $factory
     */
    public function __construct(DateTimeFactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new DateTimeFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function create($duration): \DateInterval
    {
        try {
            return $this->createOrFail($duration);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function toDateTime(\DateInterval $interval): \DateTimeImmutable
    {
        $now = $this->factory->now();

        return $now->add($interval);
    }

    /**
     * @param mixed $duration
     * @return \DateInterval
     * @throws \Exception
     */
    private function createOrFail($duration): \DateInterval
    {
        switch (true) {
            case $duration instanceof \DateInterval:
                return $duration;

            case $duration instanceof \DateTimeInterface:
                return $duration->diff($this->factory->now());

            case \is_string($duration):
                return new \DateInterval($duration);

            case \is_int($duration):
                return new \DateInterval('PT' . $duration . 'S');

            case $duration === null:
                return new \DateInterval('PT0S');

            default:
                $type = \get_debug_type($duration);
                throw new \InvalidArgumentException(\sprintf(self::ERROR_INVALID_INTERVAL_TYPE, $type));
        }
    }
}
