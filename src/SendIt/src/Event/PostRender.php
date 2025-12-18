<?php

declare(strict_types=1);

namespace Spiral\SendIt\Event;

use Spiral\Mailer\MessageInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered after the email content is rendered but before it is sent.
 *
 * Unlike {@see PreRender}, this event is dispatched when the email has already been transformed
 * into its final form (e.g., templates applied, HTML generated). Listeners can access the rendered
 * content but should avoid modifying it unless necessary (e.g., adding debug headers).
 *
 * Typical use cases:
 * - Logging the final email content
 * - Adding transport-specific headers
 * - Conditional last-minute modifications
 *
 * Example:
 * ```php
 * $dispatcher->addListener(PostRender::class, function (PostRender $event) {
 *     $event->email->getHeaders()->addTextHeader('X-Debug', '1');
 * });
 * ```
 */
final class PostRender extends Event
{
    public function __construct(
        public readonly MessageInterface $message,
        public readonly Email            $email,
    ) {}
}
