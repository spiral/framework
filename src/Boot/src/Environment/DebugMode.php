<?php

declare(strict_types=1);

namespace Spiral\Boot\Environment;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Injector\ProvideFrom;
use Spiral\Boot\Injector\InjectableEnumInterface;

#[ProvideFrom(method: 'detect')]
enum DebugMode implements InjectableEnumInterface
{
    case Enabled;
    case Disabled;

    public function isEnabled(): bool
    {
        return $this === self::Enabled;
    }

    public static function detect(EnvironmentInterface $environment): self
    {
        return \filter_var($environment->get('DEBUG'), \FILTER_VALIDATE_BOOL) ? self::Enabled : self::Disabled;
    }
}
