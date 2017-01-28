<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Core\Container;
use Spiral\Validation\Checkers\TypeChecker;

class TypeCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testNotNull()
    {
        $checker = new TypeChecker(new Container());

        $this->assertEquals(!is_null('value'), $checker->notNull('value'));
        $this->assertEquals(!is_null(1), $checker->notNull(1));
        $this->assertEquals(!is_null(0), $checker->notNull(0));
        $this->assertEquals(!is_null('0'), $checker->notNull('0'));
        $this->assertEquals(!is_null(''), $checker->notNull(''));
        $this->assertEquals(!is_null([]), $checker->notNull([]));

        $this->assertEquals(!is_null(false), $checker->notNull(false));
        $this->assertEquals(!is_null(true), $checker->notNull(true));
    }

    public function testNotEmpty()
    {
        $checker = new TypeChecker(new Container());

        $this->assertEquals(!empty('value'), $checker->notEmpty('value'));
        $this->assertEquals(!empty(1), $checker->notEmpty(1));
        $this->assertEquals(!empty(0), $checker->notEmpty(0));
        $this->assertEquals(!empty('0'), $checker->notEmpty('0'));
        $this->assertEquals(!empty(''), $checker->notEmpty(''));
        $this->assertEquals(!empty([]), $checker->notEmpty([]));

        $this->assertEquals(!empty(false), $checker->notEmpty(false));
        $this->assertEquals(!empty(true), $checker->notEmpty(true));
    }

    public function testNotEmptyStrings()
    {
        $checker = new TypeChecker(new Container());

        $this->assertTrue($checker->notEmpty('abc'));
        $this->assertTrue($checker->notEmpty(' ', false));

        $this->assertFalse($checker->notEmpty(' '));
        $this->assertFalse($checker->notEmpty(' ', true));
    }

    public function testBoolean()
    {
        $checker = new TypeChecker(new Container());

        $this->assertTrue($checker->boolean(true));
        $this->assertTrue($checker->boolean(false));
        $this->assertTrue($checker->boolean(1));
        $this->assertTrue($checker->boolean(0));

        $this->assertFalse($checker->boolean('true'));
        $this->assertFalse($checker->boolean('false'));
        $this->assertFalse($checker->boolean('0'));
        $this->assertFalse($checker->boolean('1'));
    }

    public function testDatetime()
    {
        $checker = new TypeChecker(new Container());

        $this->assertTrue($checker->datetime('now'));
        $this->assertTrue($checker->datetime('tomorrow 10am'));
        $this->assertTrue($checker->datetime(date('u')));
        $this->assertTrue($checker->datetime(time()));

        $this->assertFalse($checker->datetime('~#@'));
        $this->assertFalse($checker->datetime(''));
    }

    public function testTimezone()
    {
        $checker = new TypeChecker(new Container());

        foreach (\DateTimeZone::listIdentifiers() as $identifier) {
            $this->assertTrue($checker->timezone($identifier));
            $this->assertFalse($checker->timezone(str_rot13($identifier)));
        }
    }
}
