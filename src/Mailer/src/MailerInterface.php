<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Mailer;

use Spiral\Mailer\Exception\MailerException;

interface MailerInterface
{
    /**
     * Send one or multiple emails. Transport independent.
     *
     * @param MessageInterface ...$message
     * @throws MailerException
     */
    public function send(MessageInterface ...$message): void;
}
