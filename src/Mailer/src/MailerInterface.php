<?php

declare(strict_types=1);

namespace Spiral\Mailer;

use Spiral\Mailer\Exception\MailerException;

interface MailerInterface
{
    /**
     * Send one or multiple emails. Transport independent.
     *
     * @throws MailerException
     */
    public function send(MessageInterface ...$message): void;
}
