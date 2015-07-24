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
use Spiral\Core\StaticProxy;
use Spiral\Support\Tests\TestCase;

class ProxiesTest extends TestCase
{
    public function testBypassing()
    {
        Container::getInstance()->bind('proxy', $this);

        $this->assertSame('ABC', TestProxy::method('abc'));
        $this->assertSame('XXX', TestProxy::method('xxx'));

        Container::getInstance()->removeBinding('proxy');
    }

    public function method($value)
    {
        return strtoupper($value);
    }
}

/**
 * @method static method($argument);
 */
class TestProxy extends StaticProxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'proxy';
}