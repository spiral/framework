<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use PHPUnit\Framework\TestCase;
use Spiral\Mailer\Message;
use Spiral\SendIt\MessageSerializer;

class SerializerTest extends TestCase
{
    public function testSerializeUnserialize(): void
    {
        $mail = new Message('test', ['email@domain.com'], ['key' => 'value']);
        $mail->setFrom('admin@spiral.dev');
        $mail->setReplyTo('admin@spiral.dev');
        $mail->setCC('admin@google.com');
        $mail->setBCC('admin2@google.com');
        $mail->setOptions(['foo' => 'bar']);

        $data = MessageSerializer::pack($mail);

        $this->assertSame(
            ['subject', 'data', 'to', 'cc', 'bcc', 'from', 'replyTo', 'options'],
            array_keys($data)
        );
        $this->assertEquals($mail, MessageSerializer::unpack($data));
    }
}
