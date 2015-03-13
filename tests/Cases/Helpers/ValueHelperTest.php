<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Helpers;

use Spiral\Helpers\ValueHelper;
use Spiral\Support\Tests\TestCase;

class ValueHelperTest extends TestCase
{
    public function testString()
    {
        $this->assertInternalType('string', ValueHelper::castString('string'));
        $this->assertInternalType('string', ValueHelper::castString(0));
        $this->assertInternalType('string', ValueHelper::castString(M_PI));
        $this->assertInternalType('string', ValueHelper::castString(array('array')));
        $this->assertInternalType('string', ValueHelper::castString(array()));

        $this->assertInternalType('string', ValueHelper::castString(new \StdClass()));
        $this->assertInternalType('string', ValueHelper::castString($this));

        $this->assertInternalType('string', ValueHelper::castString($resource = fopen(__FILE__, 'rb')));
        $this->assertInternalType('string', ValueHelper::castString(fclose($resource)));

        $this->assertInternalType('string', ValueHelper::castString(null));
        $this->assertInternalType('string', ValueHelper::castString(true));
        $this->assertInternalType('string', ValueHelper::castString(false));
    }

    public function testBoolean()
    {
        $this->assertInternalType('boolean', ValueHelper::castBoolean('string'));
        $this->assertInternalType('boolean', ValueHelper::castBoolean(0));
        $this->assertInternalType('boolean', ValueHelper::castBoolean(M_PI));
        $this->assertInternalType('boolean', ValueHelper::castBoolean(array('array')));
        $this->assertInternalType('boolean', ValueHelper::castBoolean(array()));

        $this->assertInternalType('boolean', ValueHelper::castBoolean(new \StdClass()));
        $this->assertInternalType('boolean', ValueHelper::castBoolean($this));

        $this->assertInternalType('boolean', ValueHelper::castBoolean($resource = fopen(__FILE__, 'rb')));
        $this->assertInternalType('boolean', ValueHelper::castBoolean(fclose($resource)));

        $this->assertInternalType('boolean', ValueHelper::castBoolean(null));
        $this->assertInternalType('boolean', ValueHelper::castBoolean(true));
        $this->assertInternalType('boolean', ValueHelper::castBoolean(false));
    }

    public function testScalarArray()
    {
        $this->assertInternalType('array', ValueHelper::scalarArray('string'));
        $this->assertInternalType('array', ValueHelper::scalarArray(0));
        $this->assertInternalType('array', ValueHelper::scalarArray(M_PI));
        $this->assertInternalType('array', ValueHelper::scalarArray(array('array')));
        $this->assertInternalType('array', ValueHelper::scalarArray(array()));

        $this->assertSame(array(1), ValueHelper::scalarArray(array(
            new \StdClass(),
            1,
            array()
        )));

        $this->assertSame(array(1, 'abc'), ValueHelper::scalarArray(array(
            new \StdClass(),
            1,
            array(),
            'abc',
            $resource = fopen(__FILE__, 'rb')
        )));

        fclose($resource);
    }
}