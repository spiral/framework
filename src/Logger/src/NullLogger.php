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
use Psr\Log\LoggerTrait;

/**
 * Simply forwards debug messages into various locations.
 */
final class NullLogger implements LoggerInterface
{
    use LoggerTrait;

    /** @var callable */
    private $receptor;

    /** @var string */
    private $channel;

    /**
     * @param callable $receptor
     * @param string   $channel
     */
    public function __construct(callable $receptor, string $channel)
    {
        $this->receptor = $receptor;
        $this->channel = $channel;
    }

    /**
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = []): void
    {
        call_user_func($this->receptor, $this->channel, $level, $message, $context);
    }
}
