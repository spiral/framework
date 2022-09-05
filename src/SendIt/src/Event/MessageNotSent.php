<?php

declare(strict_types=1);

namespace Spiral\SendIt\Event;

use Symfony\Component\Mime\Email;

final class MessageNotSent
{
    public function __construct(
        public readonly Email $message,
        public readonly \Throwable $exception
    ) {
    }
}
