<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Spiral\Cache\Exception\StorageException;

interface TokenStorageProviderInterface
{
    /**
     * Get a token storage instance by name.
     *
     * @throws StorageException
     */
    public function getStorage(?string $name = null): TokenStorageInterface;
}
