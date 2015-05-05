<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Core;

use Spiral\Core\Container;
use Spiral\Core\Facade;
use Spiral\Support\Tests\TestCase;

class FacadesTest extends TestCase
{
    public function testBypassing()
    {
        Container::getInstance()->bind('facadeTest', $this);

        $this->assertSame('ABC', TestFacade::method('abc'));
        $this->assertSame('XXX', TestFacade::method('xxx'));

         Container::getInstance()->removeBinding('facadeTest');
    }

    public function method($value)
    {
        return strtoupper($value);
    }
}

class TestFacade extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'facadeTest';
}