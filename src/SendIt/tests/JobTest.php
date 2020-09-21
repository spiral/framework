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
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\MessageSerializer;
use Spiral\SendIt\RendererInterface;
use Spiral\SendIt\MailJob;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class JobTest extends TestCase
{
    public function testHandler(): void
    {
        $mailer = m::mock(MailerInterface::class);
        $renderer = m::mock(RendererInterface::class);
        $logger = m::mock(LoggerInterface::class);

        $handler = new MailJob(
            new MailerConfig(['from' => 'Spiral <no-reply@spiral.dev>']),
            $mailer,
            $renderer
        );
        $handler->setLogger($logger);

        $mail = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail->setFrom('admin@spiral.dev');
        $mail->setReplyTo('admin@spiral.dev');
        $mail->setCC('admin@google.com');
        $mail->setBCC('admin2@google.com');

        $email = new Email();
        $email->to('email@domain.com');
        $email->html('message body');

        $renderer->expects('render')->withArgs(function (Message $message) {
            $this->assertSame($message->getSubject(), 'test');
            return true;
        })->andReturn($email);

        $mailer->expects('send')->with($email);

        $logger->expects('debug')->with(
            'Sent `test` to "email@domain.com"',
            ['emails' => ['email@domain.com']]
        );

        $handler->handle(
            MailQueue::JOB_NAME,
            'id',
            json_encode(MessageSerializer::pack($mail))
        );
    }

    public function testHandlerError(): void
    {
        $mailer = m::mock(MailerInterface::class);
        $renderer = m::mock(RendererInterface::class);
        $logger = m::mock(LoggerInterface::class);

        $handler = new MailJob(
            new MailerConfig(['from' => 'Spiral <no-reply@spiral.dev>']),
            $mailer,
            $renderer
        );
        $handler->setLogger($logger);

        $mail = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail->setFrom('admin@spiral.dev');
        $mail->setReplyTo('admin@spiral.dev');
        $mail->setCC('admin@google.com');
        $mail->setBCC('admin2@google.com');

        $email = new Email();
        $email->to('email@domain.com');
        $email->html('message body');

        $renderer->expects('render')->withArgs(function (Message $message) {
            $this->assertSame($message->getSubject(), 'test');
            return true;
        })->andReturn($email);

        $mailer->expects('send')->with($email)->andThrow(new TransportException('failed'));

        $logger->expects('error')->with(
            'Failed to send `test` to "email@domain.com": failed',
            ['emails' => ['email@domain.com']]
        );

        try {
            $handler->handle(
                MailQueue::JOB_NAME,
                'id',
                json_encode(MessageSerializer::pack($mail))
            );
        } catch (TransportException $e) {
        }

        $logger->mockery_verify();
    }
}
