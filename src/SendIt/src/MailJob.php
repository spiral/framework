<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt;

use Spiral\Jobs\HandlerInterface;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\SendIt\Config\MailerConfig;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class MailJob implements HandlerInterface, \Spiral\Queue\HandlerInterface
{
    use LoggerTrait;

    /** @var MailerConfig */
    private $config;

    /** @var SymfonyMailer */
    private $mailer;

    /**  @var RendererInterface */
    private $renderer;

    public function __construct(MailerConfig $config, SymfonyMailer $mailer, RendererInterface $renderer)
    {
        $this->config = $config;
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     *
     * @psalm-suppress ParamNameMismatch
     */
    public function handle(string $name, string $id, $payload): void
    {
        if (\is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        if (!\is_array($payload)) {
            throw new InvalidArgumentException('Mail job payload should be an array.');
        }

        $message = MessageSerializer::unpack($payload);

        $email = $this->renderer->render($message);

        if ($email->getFrom() === []) {
            $email->from(Address::create($this->config->getFromAddress()));
        }

        $recipients = $this->getRecipients($email);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->getLogger()->error(
                sprintf(
                    'Failed to send `%s` to "%s": %s',
                    $message->getSubject(),
                    implode('", "', $recipients),
                    $e->getMessage()
                ),
                ['emails' => $recipients]
            );

            throw $e;
        }

        $this->getLogger()->debug(
            sprintf(
                'Sent `%s` to "%s"',
                $message->getSubject(),
                implode('", "', $recipients)
            ),
            ['emails' => $recipients]
        );
    }

    private function getRecipients(Email $message): array
    {
        $emails = [];

        $addresses = array_merge($message->getTo(), $message->getCc(), $message->getBcc());

        foreach ($addresses as $address) {
            $emails[] = $address->toString();
        }

        return $emails;
    }
}
