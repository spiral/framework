<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Session\Handler;

use SessionHandlerInterface;
use ReturnTypeWillChange;
/**
 * Blackhole.
 */
final class NullHandler implements SessionHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function destroy($session_id): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    #[ReturnTypeWillChange]
    public function gc($maxlifetime)
    {
        return $maxlifetime;
    }

    /**
     * @inheritdoc
     */
    public function open($save_path, $session_id): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function read($session_id): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function write($session_id, $session_data): bool
    {
        return true;
    }
}
