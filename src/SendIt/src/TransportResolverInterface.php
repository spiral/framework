<?php

declare(strict_types=1);

namespace Spiral\SendIt;

use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\TransportInterface;

interface TransportResolverInterface
{
    /**
     * Resolve a mail transport from a DSN string.
     *
     * @param string $dsn The DSN string to resolve, in the format of `smtp://user:pass@smtp.example.com:25`.
     * @throws UnsupportedSchemeException If the DSN string is not supported.
     */
    public function resolve(string $dsn): TransportInterface;
}
