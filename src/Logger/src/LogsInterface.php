<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
     *
     * @param string $channel
     * @return LoggerInterface
     */
    public function getLogger(string $channel): LoggerInterface;
}
