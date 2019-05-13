<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Http;

use Spiral\App\TestApp;
use Spiral\Framework\BaseTest;
use Spiral\Http\RrDispacher;

class RrTest extends BaseTest
{
    /** @var TestApp */
    protected $app;

    public function setUp()
    {
        $this->app = $this->makeApp();
    }

    public function testCanServe()
    {
        $this->assertFalse($this->app->get(RrDispacher::class)->canServe());
    }

    public function testCanServe2()
    {
        $this->app->getEnvironment()->set('RR_HTTP', true);
        $this->assertTrue($this->app->get(RrDispacher::class)->canServe());
    }


}