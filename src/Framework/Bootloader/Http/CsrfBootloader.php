<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Csrf\Config\CsrfConfig;

final class CsrfBootloader extends Bootloader
{
    public function init(ConfiguratorInterface $config): void
    {
        $config->setDefaults(
            CsrfConfig::CONFIG,
            [
                'cookie'   => 'csrf-token',
                'length'   => 16,
                'lifetime' => 86400,
                'secure'   => true,
                'sameSite' => null,
            ]
        );
    }
}
