<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Jobs;

use Mockery as m;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Framework\ConsoleTest;
use Spiral\Jobs\JobDispatcher;
use Spiral\RoadRunner\Worker;

class DispatcherTest extends ConsoleTest
{
    public function tearDown()
    {
        parent::tearDown();

        $fs = new Files();

        if ($fs->isDirectory(__DIR__ . '/../app/migrations')) {
            $fs->deleteDirectory(__DIR__ . '/../app/migrations');
        }

        $runtime = $this->app->get(DirectoriesInterface::class)->get('runtime');
        if ($fs->isDirectory($runtime)) {
            $fs->deleteDirectory($runtime);
        }
    }

    public function testCanServe()
    {
        $this->assertFalse($this->app->get(JobDispatcher::class)->canServe());
    }

    public function testCanServe2()
    {
        $this->app->getEnvironment()->set('RR_JOBS', true);
        $this->assertTrue($this->app->get(JobDispatcher::class)->canServe());
    }

    public function testServe()
    {
        $w = m::mock(Worker::class);

        $this->app->getEnvironment()->set('RR_JOBS', true);
        $this->app->getContainer()->bind(Worker::class, $w);

        $this->assertNull($this->app->getEnvironment()->get('FIRED'));

        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = '{
                  "id": "1", 
                  "job": "spiral.app.job.TestJob"
                }';

                return true;
            })
        )->andReturn("[]");

        $w->shouldReceive('send')->once()->andReturn(true);

        // one command only
        $w->shouldReceive('receive')->once()->andReturn(null);

        $this->app->get(JobDispatcher::class)->serve();

        $this->assertTrue($this->app->getEnvironment()->get('FIRED'));
    }

    public function testError()
    {
        $w = m::mock(Worker::class);

        $this->app->getEnvironment()->set('RR_JOBS', true);
        $this->app->getContainer()->bind(Worker::class, $w);

        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = '{
                  "id": "1", 
                  "job": "spiral.app.job.ErrorJob"
                }';

                return true;
            })
        )->andReturn("[]");

        $w->shouldReceive('error')->once()->andReturn(true);

        // one command only
        $w->shouldReceive('receive')->once()->andReturn(null);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(0, $files);

        $this->app->get(JobDispatcher::class)->serve();

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(1, $files);
    }
}