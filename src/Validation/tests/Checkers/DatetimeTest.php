<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Checkers;

use PHPUnit\Framework\TestCase;
use Spiral\Validation\Checker\DatetimeChecker;
use Spiral\Validation\ValidatorInterface;

class DatetimeTest extends TestCase
{
    /**
     * @dataProvider futureProvider
     *
     * @param bool  $expected
     * @param mixed $value
     * @param bool  $orNow
     * @param bool  $useMicroseconds
     */
    public function testFuture(bool $expected, $value, bool $orNow, bool $useMicroseconds): void
    {
        $value = $value instanceof \Closure ? $value() : $value;

        $checker = new DatetimeChecker();

        $this->assertSame($expected, $checker->future($value, $orNow, $useMicroseconds));
    }

    /**
     * @return array
     */
    public function futureProvider(): array
    {
        return [
            //the date is 100% in the future
            [true, $this->inFuture(10), false, false],
            [true, $this->inFuture(10), true, false],
            [true, $this->inFuture(10), false, true],
            [true, $this->inFuture(10), true, true],

            [true, 'tomorrow 10am', false, false],
            [true, 'now + 10 seconds', false, false],

            // the "now" date can differ in ms
            [false, 'now', false, false],
            [false, 'now', false, true], //the threshold date comes a little bit later (in ms)
            [true, 'now', true, false],
            [false, 'now', true, true], //the threshold date comes a little bit later (in ms)

            //the date is invalid, don't check after this
            [false, 'date', false, false],
            [false, 'date', true, false],
            [false, 'date', false, true],
            [false, 'date', true, true],

            [false, time() - 10, false, false],
            [false, '', false, false],
            [false, 0, false, false],
            [false, 1.1, false, false],
            [false, false, false, false],
            [false, true, false, false],
            [false, null, false, false],
            [false, [], false, false],
            [false, new \stdClass(), false, false],
        ];
    }

    /**
     * @dataProvider pastProvider
     * @param bool  $expected
     * @param mixed $value
     * @param bool  $orNow
     * @param bool  $useMicroseconds
     */
    public function testPast(bool $expected, $value, bool $orNow, bool $useMicroseconds): void
    {
        $value = $value instanceof \Closure ? $value() : $value;

        $checker = new DatetimeChecker();

        $this->assertSame($expected, $checker->past($value, $orNow, $useMicroseconds));
    }

    private function inFuture(int $seconds): \Closure
    {
        return static function () use ($seconds) {
            return \time() + $seconds;
        };
    }

    /**
     * @return array
     */
    public function pastProvider(): array
    {
        return [
            //the date is 100% in the past
            [true, time() - 10, false, false],
            [true, time() - 10, true, false],
            [true, time() - 10, false, true],
            [true, time() - 10, true, true],

            [true, 'yesterday 10am', false, false],
            [true, 'now - 10 seconds', false, false],

            //the "now" date can differ in ms
            [false, 'now', false, false],
            [true, 'now', false, true], //the threshold date comes a little bit later (in ms)
            [true, 'now', true, false],
            [true, 'now', true, true], //the threshold date comes a little bit later (in ms)

            [false, $this->inFuture(10), false, false],
            [true, '', false, false],
            [true, 0, false, false],
            [true, 1.1, false, false],
            [false, 'date', false, false],
            [false, false, false, false],
            [false, true, false, false],
            [false, null, false, false],
            [false, [], false, false],
            [false, new \stdClass(), false, false],
        ];
    }

    /**
     * @dataProvider formatProvider
     * @param bool   $expected
     * @param mixed  $value
     * @param string $format
     */
    public function testFormat(bool $expected, $value, string $format): void
    {
        $checker = new DatetimeChecker();

        $this->assertSame($expected, $checker->format($value, $format));
    }

    /**
     * @return array
     */
    public function formatProvider(): array
    {
        return [
            [true, '2019-12-27T14:27:44+00:00', 'c'], //this one is converted using other format chars
            [true, '2019-12-27T14:27:44+00:00', 'Y-m-d\TH:i:sT'], //like the 'c' one
            [true, 'Wed, 02 Oct 19 08:00:00 EST', \DateTime::RFC822],
            [true, 'Wed, 02 Oct 19 08:00:00 +0200', \DateTime::RFC822],
            [true, '2019-12-12', 'Y-m-d'],
            [true, '2019-12-12', 'Y-d-m'],
            [true, '2019-13-12', 'Y-m-d'],
            [true, '2019-12-13', 'Y-d-m'],
            [true, '2019-12-Nov', 'Y-d-M'],
            [true, '2019-12-Nov', 'Y-m-\N\o\v'],
            [false, '2019-12-Nov', 'Y-M-d'],
            [false, '2019-12-Nov', '123'],
            [false, '2019+12-Nov', 'Y-m-d'],
            [false, '-2019-12-Nov', 'Y-m-d'],
            [false, '2019-12-Abc', 'Y-d-M'],
        ];
    }

    /**
     * @dataProvider validProvider
     * @param bool  $expected
     * @param mixed $value
     */
    public function testValid(bool $expected, $value): void
    {
        $checker = new DatetimeChecker();

        $this->assertSame($expected, $checker->valid($value));
    }

    /**
     * @return array
     */
    public function validProvider(): array
    {
        return [
            [true, time() - 10,],
            [true, time(),],
            [true, date('u'),],
            [true, time() + 10,],
            [true, '',],
            [true, 'tomorrow 10am',],
            [true, 'yesterday 10am',],
            [true, 'now',],
            [true, 'now + 10 seconds',],
            [true, 'now - 10 seconds',],
            [true, 0,],
            [true, 1.1,],
            [false, 'date',],
            [false, '~#@',],
            [false, false,],
            [false, true,],
            [false, null,],
            [false, [],],
            [false, new \stdClass(),],
        ];
    }

    public function testTimezone(): void
    {
        $checker = new DatetimeChecker();

        foreach (\DateTimeZone::listIdentifiers() as $identifier) {
            $this->assertTrue($checker->timezone($identifier));
            $this->assertFalse($checker->timezone(str_rot13($identifier)));
        }

        $this->assertFalse($checker->timezone('Any zone'));
    }

    /**
     * @dataProvider beforeProvider
     * @param bool  $expected
     * @param mixed $value
     * @param mixed $threshold
     * @param bool  $orEquals
     * @param bool  $useMicroseconds
     */
    public function testBefore(bool $expected, $value, $threshold, bool $orEquals, bool $useMicroseconds): void
    {
        $this->markTestSkipped('These tests are poorly written and can cause errors. Need to rewrite');

        $value = $value instanceof \Closure ? $value() : $value;

        $checker = new DatetimeChecker();

        $mock = $this->getMockBuilder(ValidatorInterface::class)->disableOriginalConstructor()->getMock();
        $mock->method('getValue')->with('threshold')->willReturn($threshold);

        /** @var ValidatorInterface $mock */
        $this->assertSame($expected, $checker->check(
            $mock,
            'before',
            'field',
            $value,
            ['threshold', $orEquals, $useMicroseconds]
        ));
    }

    /**
     * @return array
     */
    public function beforeProvider(): array
    {
        return [
            //the date is 100% in the past
            [true, time() - 10, 'now', false, false],
            [true, time() - 10, 'now', true, false],
            [true, time() - 10, 'now', false, true],
            [true, time() - 10, 'now', true, true],

            [true, 'yesterday 10am', 'now', false, false],
            [true, 'now - 10 seconds', 'now', false, false],
            [true, 'now + 10 seconds', 'tomorrow', false, false],

            //the "now" date can differ in ms
            [false, 'now', 'now', false, false],
            [true, 'now', 'now + 1 second', false, false],
            [true, 'now', 'now', false, true], //the threshold date comes a little bit later (in ms)
            [true, 'now', 'now', true, false],
            [true, 'now', 'now', true, true], //the threshold date comes a little bit later (in ms)

            [false, time() + 10, 'now', false, false],
            [true, '', 'now', false, false],
            [true, 0, 'now', false, false],
            [true, 1.1, 'now', false, false],
            [false, 'date', 'now', false, false],
            [false, false, 'now', false, false],
            [false, true, 'now', false, false],
            [false, null, 'now', false, false],
            [false, [], 'now', false, false],
            [false, new \stdClass(), 'now', false, false],
        ];
    }

    /**
     * @dataProvider afterProvider
     * @param bool  $expected
     * @param mixed $value
     * @param mixed $threshold
     * @param bool  $orEquals
     * @param bool  $useMicroseconds
     */
    public function testAfter(bool $expected, $value, $threshold, bool $orEquals, bool $useMicroseconds): void
    {
        $this->markTestSkipped('These tests are poorly written and can cause errors. Need to rewrite');

        $value = $value instanceof \Closure ? $value() : $value;

        $checker = new DatetimeChecker();

        $mock = $this->getMockBuilder(ValidatorInterface::class)->disableOriginalConstructor()->getMock();
        $mock->method('getValue')->with('threshold')->willReturn($threshold);

        /** @var ValidatorInterface $mock */
        $this->assertSame($expected, $checker->check(
            $mock,
            'after',
            'field',
            $value,
            ['threshold', $orEquals, $useMicroseconds]
        ));
    }

    /**
     * @return array
     */
    public function afterProvider(): array
    {
        return [
            [true, $this->inFuture(10), 'now', false, false],
            [true, $this->inFuture(10), 'now', true, false],
            [true, $this->inFuture(10), 'now', false, true],
            [true, $this->inFuture(10), 'now', true, true],

            [true, 'tomorrow 10am', 'now', false, false],
            [true, 'now + 10 seconds', 'now', false, false],
            [true, 'now - 10 seconds', 'yesterday', false, false],

            //the "now" date can differ in ms
            [false, 'now', 'now', false, false],
            [true, 'now', 'now - 1 second', false, false],
            [false, 'now', 'now', false, true], //the threshold date comes a little bit later (in ms)
            [true, 'now', 'now', true, false],
            [false, 'now', 'now', true, true], //the threshold date comes a little bit later (in ms)

            [false, time() - 10, 'now', false, false],
            [false, '', 'now', false, false],
            [false, 0, 'now', false, false],
            [false, 1.1, 'now', false, false],
            [false, 'date', 'now', false, false],
            [false, false, 'now', false, false],
            [false, true, 'now', false, false],
            [false, null, 'now', false, false],
            [false, [], 'now', false, false],
            [false, new \stdClass(), 'now', false, false],
        ];
    }
}
