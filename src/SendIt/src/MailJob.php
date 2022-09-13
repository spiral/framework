<?php

declare(strict_types=1);

namespace Spiral\SendIt;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\HandlerInterface;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\Event\MessageNotSent;
use Spiral\SendIt\Event\MessageSent;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mime\Address;

final class MailJob implements HandlerInterface
{
    public function __construct(
        private readonly MailerConfig $config,
        private readonly SymfonyMailer $mailer,
        private readonly RendererInterface $renderer,
        private readonly ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function handle(string $name, string $id, string|array $payload): void
    {
        if (\is_string($payload)) {
            $payload = \json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!\is_array($payload)) {
            throw new InvalidArgumentException('Mail job payload should be an array.');
        }

        $message = MessageSerializer::unpack($payload);

        $email = $this->renderer->render($message);

        if ($email->getFrom() === []) {
            $email->from(Address::create($this->config->getFromAddress()));
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->dispatcher?->dispatch(new MessageNotSent($email, $e));

            throw $e;
        }

        $this->dispatcher?->dispatch(new MessageSent($email));
    }
}
