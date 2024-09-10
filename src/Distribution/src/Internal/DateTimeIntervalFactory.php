<?php

declare(strict_types=1);

namespace Spiral\Distribution\Internal;

/**
 * @internal DateTimeIntervalFactory is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Distribution
 */
final class DateTimeIntervalFactory implements DateTimeIntervalFactoryInterface
{
    private const ERROR_INVALID_INTERVAL_TYPE = 'The value of type `%s` is not a valid date interval type.';

    public function __construct(
        private readonly DateTimeFactoryInterface $factory = new DateTimeFactory()
    ) {
    }

    public function create(mixed $duration): \DateInterval
    {
        try {
            return $this->createOrFail($duration);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function toDateTime(\DateInterval $interval): \DateTimeImmutable
    {
        /** @psalm-suppress InternalMethod */
        return $this->factory->now()->add($interval);
    }

    /**
     * @throws \Exception
     * @psalm-suppress InternalMethod
     */
    private function createOrFail(mixed $duration): \DateInterval
    {
        return match (true) {
            $duration instanceof \DateInterval => $duration,
            $duration instanceof \DateTimeInterface => $this->factory->now()->diff($duration),
            \is_string($duration) => new \DateInterval($duration),
            \is_int($duration) => new \DateInterval('PT' . $duration . 'S'),
            $duration === null => new \DateInterval('PT0S'),
            default => throw new \InvalidArgumentException(
                \sprintf(self::ERROR_INVALID_INTERVAL_TYPE, \get_debug_type($duration))
            )
        };
    }
}
