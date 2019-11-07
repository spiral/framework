<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Http;

use Mockery as m;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Spiral\Framework\ConsoleTest;
use Spiral\Http\RrDispacher;
use Spiral\RoadRunner\PSR7Client;
use Zend\Diactoros\ServerRequest;

class RrTest extends ConsoleTest
{
    public function testCanServe(): void
    {
        $this->assertFalse($this->app->get(RrDispacher::class)->canServe());
    }

    public function testCanServe2(): void
    {
        $this->app->getEnvironment()->set('RR_HTTP', true);
        $this->assertTrue($this->app->get(RrDispacher::class)->canServe());
    }

    public function testServe(): void
    {
        $this->app->getEnvironment()->set('RR_HTTP', true);

        $psr = m::mock(PSR7Client::class);

        $psr->shouldReceive('acceptRequest')->once()->andReturn(
            new ServerRequest([], [], '/index/dave')
        );

        $psr->shouldReceive('respond')->once()->with(
            \Mockery::on(function ($r) {
                $this->assertSame('Hello, dave.', (string)$r->getBody());
                return true;
            })
        )->andReturn(true);

        $psr->shouldReceive('acceptRequest')->once()->andReturn(null);

        $this->app->get(RrDispacher::class)->serve($psr);
    }

    public function testServeError(): void
    {
        $this->app->getEnvironment()->set('RR_HTTP', true);

        $psr = m::mock(PSR7Client::class);

        $psr->shouldReceive('acceptRequest')->once()->andReturn(
            new ServerRequest([], [], '/error')
        );

        $psr->shouldReceive('respond')->once()->with(
            \Mockery::on(function ($r) {
                $this->assertContains('500', (string)$r->getBody());
                return true;
            })
        )->andReturn(true);

        $psr->shouldReceive('acceptRequest')->once()->andReturn(null);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(0, $files);

        $this->app->get(RrDispacher::class)->serve($psr);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(1, $files);
    }

    public function testServeErrorDebug(): void
    {
        $this->app = $this->makeApp([
            'DEBUG' => true
        ]);

        $this->app->getEnvironment()->set('RR_HTTP', true);

        $psr = m::mock(PSR7Client::class);

        $psr->shouldReceive('acceptRequest')->once()->andReturn(
            new ServerRequest([], [], '/error')
        );

        $psr->shouldReceive('respond')->once()->with(
            \Mockery::on(function ($r) {
                $this->assertContains('undefined', (string)$r->getBody());
                return true;
            })
        )->andReturn(true);

        $psr->shouldReceive('acceptRequest')->once()->andReturn(null);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(0, $files);

        $this->app->get(RrDispacher::class)->serve($psr);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(1, $files);
    }
}
