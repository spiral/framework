<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Mailer\Message;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\MessageSerializer;
use Spiral\SendIt\RendererInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class JobTest extends TestCase
{
    /** @var MailerInterface */
    protected $mailer;
    /** @var RendererInterface */
    protected $renderer;
    /** @var LoggerInterface */
    protected $logger;

    public function setUp(): void
    {
        parent::setUp();

        $this->mailer = m::mock(MailerInterface::class);
        $this->renderer = m::mock(RendererInterface::class);
        $this->logger = m::mock(LoggerInterface::class);
    }

    public function testHandler(): void
    {
        $email = $this->getEmail();

        $this->expectRenderer($email);

        $this->mailer->expects('send')->with($email);

        $this->logger->expects('debug')->with(
            'Sent `test` to "email@domain.com"',
            ['emails' => ['email@domain.com']]
        );

        $this->getHandler()->handle(
            MailQueue::JOB_NAME,
            'id',
            json_encode(MessageSerializer::pack($this->getMail()))
        );
    }

    public function testHandlerError(): void
    {
        $email = $this->getEmail();

        $this->expectRenderer($email);

        $this->mailer->expects('send')->with($email)->andThrow(new TransportException('failed'));

        $this->logger->expects('error')->with(
            'Failed to send `test` to "email@domain.com": failed',
            ['emails' => ['email@domain.com']]
        );

        try {
            $this->getHandler()->handle(
                MailQueue::JOB_NAME,
                'id',
                json_encode(MessageSerializer::pack($this->getMail()))
            );
        } catch (TransportException $e) {
        }

        $this->logger->mockery_verify();
    }

    private function getEmail(): Email
    {
        $email = new Email();
        $email->to('email@domain.com');
        $email->html('message body');
        return $email;
    }

    private function expectRenderer(Email $email): void
    {
        $this->renderer->expects('render')->withArgs(
            function (Message $message) {
                $this->assertSame($message->getSubject(), 'test');
                return true;
            }
        )->andReturn($email);
    }

    private function getHandler(): MailJob
    {
        $handler = new MailJob(
            new MailerConfig(['from' => 'no-reply@spiral.dev']),
            $this->mailer,
            $this->renderer
        );
        $handler->setLogger($this->logger);
        return $handler;
    }

    private function getMail(): Message
    {
        $mail = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail->setFrom('admin@spiral.dev');
        $mail->setReplyTo('admin@spiral.dev');
        $mail->setCC('admin@google.com');
        $mail->setBCC('admin2@google.com');
        return $mail;
    }
}
