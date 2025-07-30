<?php

declare(strict_types=1);

namespace Spiral\SendIt\Event;

use Spiral\Mailer\MessageInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\Event;

final class PostRender extends Event
{
    public function __construct(
        public readonly MessageInterface $message,
        public readonly Email            $email,
    ) {}
}
