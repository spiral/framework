<?php

declare(strict_types=1);

namespace Spiral\Mailer\Event;

use Spiral\Mailer\MessageInterface;

final class MessageSent
{
    public function __construct(
        public readonly MessageInterface $message
    ) {
    }
}
