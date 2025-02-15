<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Queue\Options;
use Spiral\Queue\QueueInterface;
use Spiral\Mailer\Message;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\MessageSerializer;

class JobsQueueTest extends TestCase
{
    public function testQueue(): void
    {
        $queue = m::mock(QueueInterface::class);

        $mailer = new MailQueue(
            new MailerConfig([
                'queue' => 'mailer',
            ]),
            $queue,
        );

        $mail = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail->setFrom('admin@spiral.dev');
        $mail->setReplyTo('admin@spiral.dev');
        $mail->setCC('admin@google.com');
        $mail->setBCC('admin2@google.com');

        $queue->expects('push')->withArgs(
            static function ($job, $data, Options $options) use ($mail): bool {
                self::assertSame(MailQueue::JOB_NAME, $job);
                self::assertSame($data, MessageSerializer::pack($mail));
                self::assertSame('mailer', $options->getQueue());

                return true;
            },
        );

        $mailer->send($mail);
    }
}
