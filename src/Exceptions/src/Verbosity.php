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
        return match ($environment->get('VERBOSITY')) {
            'basic' => self::BASIC,
            'debug' => self::DEBUG,
            default => self::VERBOSE
        };
    }
}
