<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Mailer\Message;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\Event\MessageNotSent;
use Spiral\SendIt\Event\MessageSent;
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

    public function setUp(): void
    {
        parent::setUp();

        $this->mailer = m::mock(MailerInterface::class);
        $this->renderer = m::mock(RendererInterface::class);
    }

    public function testHandler(): void
    {
        $email = $this->getEmail();

        $this->expectRenderer($email);

        $this->mailer->expects('send')->with($email);

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

        try {
            $this->getHandler()->handle(
                MailQueue::JOB_NAME,
                'id',
                json_encode(MessageSerializer::pack($this->getMail()))
            );
        } catch (TransportException) {
        }
    }

    public function testMessageSentEventShouldBeDispatched(): void
    {
        $email = $this->getEmail();

        $this->expectRenderer($email);

        $this->mailer->expects('send')->with($email);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new MessageSent($email));

        $this->getHandler($dispatcher)->handle(
            MailQueue::JOB_NAME,
            'id',
            \json_encode(MessageSerializer::pack($this->getMail()))
        );
    }

    public function testMessageNotSentEventShouldBeDispatched(): void
    {
        $email = $this->getEmail();
        $exception = new TransportException('failed');

        $this->expectRenderer($email);
        $this->mailer->expects('send')->with($email)->andThrow($exception);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new MessageNotSent($email, $exception));

        $this->expectException(TransportException::class);
        $this->getHandler($dispatcher)->handle(
            MailQueue::JOB_NAME,
            'id',
            \json_encode(MessageSerializer::pack($this->getMail()))
        );
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

    private function getHandler(?EventDispatcherInterface $dispatcher = null): MailJob
    {
        return new MailJob(
            new MailerConfig(['from' => 'no-reply@spiral.dev']),
            $this->mailer,
            $this->renderer,
            $dispatcher
        );
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
