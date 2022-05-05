<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt;

use Spiral\Mailer\Message;
use Spiral\Mailer\MessageInterface;

final class MessageSerializer
{
    public static function pack(MessageInterface $message): array
    {
        return [
            'subject' => $message->getSubject(),
            'data'    => $message->getData(),
            'to'      => $message->getTo(),
            'cc'      => $message->getCC(),
            'bcc'     => $message->getBCC(),
            'from'    => $message->getFrom(),
            'replyTo' => $message->getReplyTo(),
            'options' => $message->getOptions(),
        ];
    }

    public static function unpack(array $payload): MessageInterface
    {
        $message = new Message($payload['subject'], $payload['to'], $payload['data']);
        if ($payload['from'] !== null) {
            $message->setFrom($payload['from']);
        }

        if ($payload['replyTo'] !== null) {
            $message->setReplyTo($payload['replyTo']);
        }

        $message->setCC(...$payload['cc']);
        $message->setBCC(...$payload['bcc']);
        $message->setOptions($payload['options']);

        return $message;
    }
}
