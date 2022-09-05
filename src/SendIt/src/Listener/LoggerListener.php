<?php

declare(strict_types=1);

namespace Spiral\SendIt\Listener;

use Psr\Log\LoggerInterface;
use Spiral\SendIt\Event\MessageNotSent;
use Spiral\SendIt\Event\MessageSent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class LoggerListener
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function onMessageSent(MessageSent $event): void
    {
        $this->logger->debug(
            \sprintf(
                'Sent `%s` to "%s"',
                $event->message->getSubject(),
                \implode('", "', $this->getRecipients($event->message))
            ),
            ['emails' => $this->getRecipients($event->message)]
        );
    }

    public function onMessageNotSent(MessageNotSent $event): void
    {
        $this->logger->error(
            \sprintf(
                'Failed to send `%s` to "%s": %s',
                $event->message->getSubject(),
                \implode('", "', $this->getRecipients($event->message)),
                $event->exception->getMessage()
            ),
            ['emails' => $this->getRecipients($event->message)]
        );
    }

    private function getRecipients(Email $message): array
    {
        return \array_map(
            static fn (Address $address): string => $address->toString(),
            \array_merge($message->getTo(), $message->getCc(), $message->getBcc())
        );
    }
}
