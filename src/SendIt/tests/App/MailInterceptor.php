<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\SendIt\App;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class MailInterceptor implements MailerInterface
{
    private $last;

    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        $this->last = $message;
    }

    public function getLast(): Email
    {
        return $this->last;
    }
}
