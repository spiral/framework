<?php

declare(strict_types=1);

namespace Spiral\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use Spiral\Mailer\Message;

class MessageTest extends TestCase
{
    public function testDefaults(): void
    {
        $m = new Message('test', 'email@domain.com');
        $this->assertSame('test', $m->getSubject());
        $this->assertSame(['email@domain.com'], $m->getTo());
        $this->assertSame([], $m->getData());
        $this->assertSame([], $m->getCC());
        $this->assertSame([], $m->getBCC());
        $this->assertNull($m->getFrom());
        $this->assertNull($m->getReplyTo());
    }

    public function testData(): void
    {
        $m = new Message('test', 'email@domain.com');
        $this->assertSame([], $m->getData());

        $m->setData(['hello' => 'world']);
        $this->assertSame(['hello' => 'world'], $m->getData());
    }

    public function testOptions(): void
    {
        $m = new Message('test', 'email@domain.com');
        $this->assertSame([], $m->getOptions());

        $m->setOptions(['hello' => 'world']);
        $this->assertSame(['hello' => 'world'], $m->getOptions());

        $m->setOption('k', 'v');
        $this->assertSame(['hello' => 'world', 'k' => 'v'], $m->getOptions());
    }

    public function testTo(): void
    {
        $m = new Message('test', ['email@domain.com', 'email2@domain.com']);
        $this->assertSame(['email@domain.com', 'email2@domain.com'], $m->getTo());

        $m->setTo('email@domain.com');
        $this->assertSame(['email@domain.com'], $m->getTo());

        $m->setTo('email@domain.com', 'another@domain.com');
        $this->assertSame(['email@domain.com', 'another@domain.com'], $m->getTo());

        $m->setTo();
        $this->assertSame([], $m->getTo());
    }

    public function testCC(): void
    {
        $m = new Message('test', 'email@domain.com');

        $this->assertSame([], $m->getCC());
        $m->setCC('email@domain.com');
        $this->assertSame(['email@domain.com'], $m->getCC());

        $m->setCC('email@domain.com', 'another@domain.com');
        $this->assertSame(['email@domain.com', 'another@domain.com'], $m->getCC());

        $m->setCC();
        $this->assertSame([], $m->getCC());
    }

    public function testBCC(): void
    {
        $m = new Message('test', 'email@domain.com');

        $this->assertSame([], $m->getBCC());
        $m->setBCC('email@domain.com');
        $this->assertSame(['email@domain.com'], $m->getBCC());

        $m->setBCC('email@domain.com', 'another@domain.com');
        $this->assertSame(['email@domain.com', 'another@domain.com'], $m->getBCC());

        $m->setBCC();
        $this->assertSame([], $m->getBCC());
    }

    public function testFrom(): void
    {
        $m = new Message('test', 'email@domain.com');

        $this->assertNull($m->getFrom());

        $m->setFrom('email@domain.com');
        $this->assertSame('email@domain.com', $m->getFrom());

        $m->setFrom(null);
        $this->assertNull($m->getFrom());
    }

    public function testReplyTo(): void
    {
        $m = new Message('test', 'email@domain.com');

        $this->assertNull($m->getReplyTo());

        $m->setReplyTo('email@domain.com');
        $this->assertSame('email@domain.com', $m->getReplyTo());

        $m->setReplyTo(null);
        $this->assertNull($m->getReplyTo());
    }

    public function testSetDelayInSeconds(): void
    {
        $m = new Message('test', 'email@domain.com');
        $m->setDelay(100);

        $this->assertSame([
            'delay' => 100
        ], $m->getOptions());
    }

    public function testSetDelayInDateInterval(): void
    {
        $m = new Message('test', 'email@domain.com');
        $m->setDelay(new \DateInterval('PT56S'));

        $this->assertSame([
            'delay' => 56
        ], $m->getOptions());
    }

    public function testSetDelayInDateTime(): void
    {
        $m = new Message('test', 'email@domain.com');
        $m->setDelay(new \DateTimeImmutable('+ 123 second'));

        $this->assertSame([
            'delay' => 123
        ], $m->getOptions());
    }

    public function testSetDelayInDateTimeWithPastTime(): void
    {
        $m = new Message('test', 'email@domain.com');
        $m->setDelay(new \DateTimeImmutable('- 123 second'));

        $this->assertSame([
            'delay' => 0
        ], $m->getOptions());
    }
}
