<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Tests\Cases\Core;

use Spiral\Core\Container;
use Spiral\Core\Service;

class ServicesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     */
    public function testPropertiesFailure()
    {
        $container = new Container();
        $service = new ServiceFixture($container);

        $service->getAbc();
    }

    public function testPropertiesSuccess()
    {
        $container = new Container();
        $container->bind('abc', function () {
            return 'test';
        });

        $service = new ServiceFixture($container);
        $this->assertEquals('test', $service->getAbc());
    }
}

class ServiceFixture extends Service
{
    public function getAbc()
    {
        return $this->abc;
    }
}