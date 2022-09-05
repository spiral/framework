<?php

declare(strict_types=1);

namespace Spiral\SendIt\Event;

use Symfony\Component\Mime\Email;

final class MessageSent
{
    public function __construct(
        public readonly Email $message
    ) {
    }
}
