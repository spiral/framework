<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Mailer\Message;
use Spiral\Queue\Options;
use Spiral\Queue\QueueInterface;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\MessageSerializer;

class QueueTest extends TestCase
{
    /** @var m\LegacyMockInterface|m\MockInterface|QueueInterface */
    private $queue;

    private MailQueue $mailer;

    public function testQueue(): void
    {
        $mail = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail->setFrom('admin@spiral.dev');
        $mail->setReplyTo('admin@spiral.dev');
        $mail->setCC('admin@google.com');
        $mail->setBCC('admin2@google.com');

        $this->queue->expects('push')->withArgs(
            static function ($job, $data, Options $options) use ($mail): bool {
                self::assertSame(MailQueue::JOB_NAME, $job);
                self::assertSame($data, MessageSerializer::pack($mail));
                self::assertSame('mailer', $options->getQueue());
                self::assertNull($options->getDelay());

                return true;
            },
        );

        $this->mailer->send($mail);
    }

    public function testQueueWithDelay(): void
    {
        $mail1 = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail1->setDelay(new \DateInterval('PT30S'));

        $mail2 = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail2->setDelay((new \DateTimeImmutable('+100 second')));

        $mail3 = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail3->setDelay(200);

        $this->queue->expects('push')->once()->withArgs(
            static function ($job, $data, Options $options): bool {
                self::assertSame(30, $options->getDelay());
                return true;
            },
        );

        $this->queue->expects('push')->once()->withArgs(
            static function ($job, $data, Options $options): bool {
                self::assertSame(100, $options->getDelay());
                return true;
            },
        );

        $this->queue->expects('push')->once()->withArgs(
            static function ($job, $data, Options $options): bool {
                self::assertSame(200, $options->getDelay());
                return true;
            },
        );

        $this->mailer->send($mail1, $mail2, $mail3);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = m::mock(QueueInterface::class);

        $this->mailer = new MailQueue(
            new MailerConfig([
                'queue' => 'mailer',
            ]),
            $this->queue,
        );
    }
}
