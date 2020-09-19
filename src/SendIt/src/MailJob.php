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
use Spiral\SendIt\Config\MailerConfig;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class MailJob implements HandlerInterface
{
    use LoggerTrait;

    /** @var MailerConfig */
    private $config;

    /** @var SymfonyMailer */
    private $mailer;

    /**  @var RendererInterface */
    private $renderer;

    /**
     * @param MailerConfig      $config
     * @param SymfonyMailer     $mailer
     * @param RendererInterface $renderer
     */
    public function __construct(MailerConfig $config, SymfonyMailer $mailer, RendererInterface $renderer)
    {
        $this->config = $config;
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    /**
     * @param string $jobType
     * @param string $jobID
     * @param string $payload
     * @throws TransportExceptionInterface
     */
    public function handle(string $jobType, string $jobID, string $payload): void
    {
        $message = MessageSerializer::unpack(json_decode($payload, true));

        $email = $this->renderer->render($message);

        if ($email->getFrom() === []) {
            $email->from(Address::fromString($this->config->getFromAddress()));
        }

        $recipients = $this->getRecipients($email);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->getLogger()->error(
                sprintf(
                    'Failed to send `%s` to "%s": %s',
                    $message->getSubject(),
                    join('", "', $recipients),
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
                join('", "', $recipients)
            ),
            ['emails' => $recipients]
        );
    }

    /**
     * @param Email $message
     * @return array
     */
    private function getRecipients(Email $message): array
    {
        $emails = [];

        /** @var Address[] $addresses */
        $addresses = array_merge($message->getTo(), $message->getCc(), $message->getBcc());

        foreach ($addresses as $addr) {
            $emails[] = $addr->toString();
        }

        return $emails;
    }
}
