<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt\App;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class MailInterceptor implements MailerInterface
{
    private RawMessage $last;

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        $this->last = $message;
    }

    public function getLast(): RawMessage
    {
        return $this->last;
    }
}
