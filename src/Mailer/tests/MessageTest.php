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
        self::assertSame('test', $m->getSubject());
        self::assertSame(['email@domain.com'], $m->getTo());
        self::assertSame([], $m->getData());
        self::assertSame([], $m->getCC());
        self::assertSame([], $m->getBCC());
        self::assertNull($m->getFrom());
        self::assertNull($m->getReplyTo());
    }

    public function testData(): void
    {
        $m = new Message('test', 'email@domain.com');
        self::assertSame([], $m->getData());

        $m->setData(['hello' => 'world']);
        self::assertSame(['hello' => 'world'], $m->getData());
    }

    public function testOptions(): void
    {
        $m = new Message('test', 'email@domain.com');
        self::assertSame([], $m->getOptions());

        $m->setOptions(['hello' => 'world']);
        self::assertSame(['hello' => 'world'], $m->getOptions());

        $m->setOption('k', 'v');
        self::assertSame(['hello' => 'world', 'k' => 'v'], $m->getOptions());
    }

    public function testTo(): void
    {
        $m = new Message('test', ['email@domain.com', 'email2@domain.com']);
        self::assertSame(['email@domain.com', 'email2@domain.com'], $m->getTo());

        $m->setTo('email@domain.com');
        self::assertSame(['email@domain.com'], $m->getTo());

        $m->setTo('email@domain.com', 'another@domain.com');
        self::assertSame(['email@domain.com', 'another@domain.com'], $m->getTo());

        $m->setTo();
        self::assertSame([], $m->getTo());
    }

    public function testCC(): void
    {
        $m = new Message('test', 'email@domain.com');

        self::assertSame([], $m->getCC());
        $m->setCC('email@domain.com');
        self::assertSame(['email@domain.com'], $m->getCC());

        $m->setCC('email@domain.com', 'another@domain.com');
        self::assertSame(['email@domain.com', 'another@domain.com'], $m->getCC());

        $m->setCC();
        self::assertSame([], $m->getCC());
    }

    public function testBCC(): void
    {
        $m = new Message('test', 'email@domain.com');

        self::assertSame([], $m->getBCC());
        $m->setBCC('email@domain.com');
        self::assertSame(['email@domain.com'], $m->getBCC());

        $m->setBCC('email@domain.com', 'another@domain.com');
        self::assertSame(['email@domain.com', 'another@domain.com'], $m->getBCC());

        $m->setBCC();
        self::assertSame([], $m->getBCC());
    }

    public function testFrom(): void
    {
        $m = new Message('test', 'email@domain.com');

        self::assertNull($m->getFrom());

        $m->setFrom('email@domain.com');
        self::assertSame('email@domain.com', $m->getFrom());

        $m->setFrom(null);
        self::assertNull($m->getFrom());
    }

    public function testReplyTo(): void
    {
        $m = new Message('test', 'email@domain.com');

        self::assertNull($m->getReplyTo());

        $m->setReplyTo('email@domain.com');
        self::assertSame('email@domain.com', $m->getReplyTo());

        $m->setReplyTo(null);
        self::assertNull($m->getReplyTo());
    }

    public function testSetDelayInSeconds(): void
    {
        $m = new Message('test', 'email@domain.com');
        $m->setDelay(100);

        self::assertSame([
            'delay' => 100
        ], $m->getOptions());
    }

    public function testSetDelayInDateInterval(): void
    {
        $m = new Message('test', 'email@domain.com');
        $m->setDelay(new \DateInterval('PT56S'));

        self::assertSame([
            'delay' => 56
        ], $m->getOptions());
    }

    public function testSetDelayInDateTime(): void
    {
        $m = new Message('test', 'email@domain.com');
        $m->setDelay(new \DateTimeImmutable('+ 123 second'));

        self::assertSame([
            'delay' => 123
        ], $m->getOptions());
    }

    public function testSetDelayInDateTimeWithPastTime(): void
    {
        $m = new Message('test', 'email@domain.com');
        $m->setDelay(new \DateTimeImmutable('- 123 second'));

        self::assertSame([
            'delay' => 0
        ], $m->getOptions());
    }
}
