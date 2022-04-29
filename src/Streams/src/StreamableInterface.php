<?php

declare(strict_types=1);

namespace Spiral\Streams;

use Psr\Http\Message\StreamInterface;

/**
 * Class contain PSR-7 compatible body.
 */
interface StreamableInterface
{
    public function getStream(): StreamInterface;
}
