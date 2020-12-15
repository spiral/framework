<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Tests\Validation\BaseTest;
use Spiral\Validation\Checker\TypeChecker;

class TypesTest extends BaseTest
{
    public function testNotNull(): void
    {
        /** @var TypeChecker $checker */
        $checker = $this->container->get(TypeChecker::class);

        $this->assertTrue($checker->notNull('value'));
        $this->assertTrue($checker->notNull(1));
        $this->assertTrue($checker->notNull(0));
        $this->assertTrue($checker->notNull('0'));
        $this->assertTrue($checker->notNull(''));
        $this->assertTrue($checker->notNull([]));

        $this->assertTrue($checker->notNull(false));
        $this->assertTrue($checker->notNull(true));
        $this->assertTrue($checker->notNull(new \stdClass()));
        $this->assertFalse($checker->notNull(null));
    }

    public function testNotEmpty(): void
    {
        /** @var TypeChecker $checker */
        $checker = $this->container->get(TypeChecker::class);

        $this->assertEquals(!empty('value'), $checker->notEmpty('value'));
        $this->assertEquals(!empty(1), $checker->notEmpty(1));
        $this->assertEquals(!empty(0), $checker->notEmpty(0));
        $this->assertEquals(!empty('0'), $checker->notEmpty('0'));
        $this->assertEquals(!empty(''), $checker->notEmpty(''));
        $this->assertEquals(!empty([]), $checker->notEmpty([]));

        $this->assertEquals(!empty(false), $checker->notEmpty(false));
        $this->assertEquals(!empty(true), $checker->notEmpty(true));
    }

    public function testNotEmptyStrings(): void
    {
        /** @var TypeChecker $checker */
        $checker = $this->container->get(TypeChecker::class);

        $this->assertTrue($checker->notEmpty('abc'));
        $this->assertTrue($checker->notEmpty(' ', false));

        $this->assertFalse($checker->notEmpty(' '));
        $this->assertFalse($checker->notEmpty(' ', true));
    }

    public function testBoolean(): void
    {
        /** @var TypeChecker $checker */
        $checker = $this->container->get(TypeChecker::class);

        $this->assertTrue($checker->boolean(true));
        $this->assertTrue($checker->boolean(false));
        $this->assertTrue($checker->boolean(1));
        $this->assertTrue($checker->boolean(0));

        $this->assertFalse($checker->boolean('true'));
        $this->assertFalse($checker->boolean('false'));
        $this->assertFalse($checker->boolean('0'));
        $this->assertFalse($checker->boolean('1'));
    }

    public function testDatetime(): void
    {
        /** @var TypeChecker $checker */
        $checker = $this->container->get(TypeChecker::class);

        $this->assertTrue($checker->datetime('now'));
        $this->assertTrue($checker->datetime('tomorrow 10am'));
        $this->assertTrue($checker->datetime(date('u')));
        $this->assertTrue($checker->datetime(time()));

        $this->assertFalse($checker->datetime('date'));
        $this->assertFalse($checker->datetime(''));

        $this->assertFalse($checker->datetime([]));
        $this->assertFalse($checker->datetime(null));
    }

    public function testTimezone(): void
    {
        /** @var TypeChecker $checker */
        $checker = $this->container->get(TypeChecker::class);

        foreach (\DateTimeZone::listIdentifiers() as $identifier) {
            $this->assertTrue($checker->timezone($identifier));
            $this->assertFalse($checker->timezone(str_rot13($identifier)));
        }
    }

    public function testArrayOf(): void
    {
        /** @var TypeChecker $checker */
        $checker = $this->container->get(TypeChecker::class);

        $this->assertTrue($checker->arrayOf([1], 'is_int'));
        $this->assertTrue($checker->arrayOf([1], 'integer'));
        $this->assertTrue($checker->arrayOf(['1'], 'is_string'));

        $this->assertFalse($checker->arrayOf(1, 'is_int'));
        $this->assertFalse($checker->arrayOf([1], 'is_string'));
    }
}
