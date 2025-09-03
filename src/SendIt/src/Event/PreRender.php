<?php

declare(strict_types=1);

namespace Spiral\SendIt\Event;

use Spiral\Mailer\MessageInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered before the email content is rendered.
 *
 * This event allows you to modify the {@see Email} object (e.g., headers, body, or attachments)
 * before it is passed to the mailer for sending. The original {@see MessageInterface} is provided
 * for context but should not be modified.
 *
 * Example usage (listener):
 * ```
 * $dispatcher->addListener(PreRender::class, function (PreRender $event) {
 *     $event->email->addHeader('X-Custom', 'value');
 * });
 * ```
 */
final class PreRender extends Event
{
    public function __construct(
        public readonly MessageInterface $message,
        public readonly Email            $email,
    ) {}
}
