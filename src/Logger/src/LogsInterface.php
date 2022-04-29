<?php

declare(strict_types=1);

namespace Spiral\Logger;

use Psr\Log\LoggerInterface;

/**
 * LogsInterface is generic log factory interface.
 */
interface LogsInterface
{
    /**
     * Get pre-configured logger instance.
     */
    public function getLogger(string $channel): LoggerInterface;
}
