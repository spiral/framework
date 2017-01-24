<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests;

use Spiral\Tests\Core\Fixtures\SharedComponent;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestApplication
     */
    protected $app;

    public function setUp()
    {
        $root = __DIR__ . '/app/';
        $this->app = TestApplication::init([
            'root'        => $root,
            'libraries'   => $root . 'vendor/',
            'application' => $root . 'app/',
            'runtime'     => $root . 'runtime/',
            'cache'       => $root . 'runtime/cache/',
        ]);

        //Open application scope
        SharedComponent::shareContainer($this->app->container);
    }

    public function tearDown()
    {
        //Close scope
        SharedComponent::shareContainer(null);
    }
}