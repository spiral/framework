<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Auth\TokenStorage;

use Spiral\Auth\Session\TokenStorage as SessionStorage;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageProvider;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Auth\HttpAuthBootloader;
use Spiral\Bootloader\Http\SessionBootloader;

/**
 * Stores authentication token in session.
 * @deprecated use {@see TokenStorageProvider} instead.
 */
final class SessionTokensBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        HttpAuthBootloader::class,
        SessionBootloader::class,
    ];

    protected const SINGLETONS = [
        TokenStorageInterface::class => SessionStorage::class,
    ];
}
