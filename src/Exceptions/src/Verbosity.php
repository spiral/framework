<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Injector\InjectableEnumInterface;
use Spiral\Boot\Injector\ProvideFrom;

#[ProvideFrom(method: 'detect')]
enum Verbosity: int implements InjectableEnumInterface
{
    case BASIC = 0;
    case VERBOSE = 1;
    case DEBUG = 2;

    public static function detect(EnvironmentInterface $environment): self
    {
        return match (\strtolower((string) $environment->get('VERBOSITY_LEVEL'))) {
            'basic', '0' => self::BASIC,
            'debug', '2' => self::DEBUG,
            default => self::VERBOSE
        };
    }
}
