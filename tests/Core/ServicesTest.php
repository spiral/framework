<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Tests\Core;

use Spiral\Core\Container;
use Spiral\Core\Service;
use Spiral\Tests\Core\Fixtures\SharedComponent;

class ServicesTest extends \PHPUnit_Framework_TestCase
{
    protected $container;

    public function setUp()
    {
        $this->container = new Container();

        SharedComponent::shareContainer($this->container);
    }

    public function tearDown()
    {
        SharedComponent::shareContainer(null);
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     */
    public function testPropertiesFailure()
    {
        $service = new ServiceFixture();

        $service->getAbc();
    }

    public function testPropertiesSuccess()
    {
        $this->container->bind('abc', function () {
            return 'test';
        });

        $service = new ServiceFixture();
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