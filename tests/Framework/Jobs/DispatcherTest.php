<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Jobs;

use Mockery as m;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Spiral\Jobs\JobDispatcher;
use Spiral\RoadRunner\Worker;
use Spiral\Tests\App\Job\ErrorJob;
use Spiral\Tests\App\Job\TestJob;
use Spiral\Tests\Framework\ConsoleTest;

class DispatcherTest extends ConsoleTest
{
    public function testCanServe(): void
    {
        $this->assertFalse($this->app->get(JobDispatcher::class)->canServe());
    }

    public function testCanServe2(): void
    {
        $this->app->getEnvironment()->set('RR_JOBS', true);
        $this->assertTrue($this->app->get(JobDispatcher::class)->canServe());
    }

    public function testServe(): void
    {
        $w = m::mock(Worker::class);

        $this->app->getEnvironment()->set('RR_JOBS', true);
        $this->app->getContainer()->bind(Worker::class, $w);

        $this->assertNull($this->app->getEnvironment()->get('FIRED'));

        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = $this->arrayToContextString(TestJob::class);

                return true;
            })
        )->andReturn('[]');

        $w->shouldReceive('send')->once()->andReturn(true);

        // one command only
        $w->shouldReceive('receive')->once()->andReturn(null);

        $this->app->get(JobDispatcher::class)->serve();

        $this->assertTrue($this->app->getEnvironment()->get('FIRED'));
    }

    public function testError(): void
    {
        $w = m::mock(Worker::class);

        $this->app->getEnvironment()->set('RR_JOBS', true);
        $this->app->getContainer()->bind(Worker::class, $w);

        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = $this->arrayToContextString(ErrorJob::class);

                return true;
            })
        )->andReturn('[]');

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

    /**
     * @param string $class
     * @param int $id
     * @return string
     */
    protected function arrayToContextString(string $class, int $id = 1): string
    {
        $signature = \explode('\\', $class);
        $class = \array_pop($signature);

        return \json_encode([
            'id'  => (string)$id,
            'job' => \strtolower(\implode('.', $signature)) . '.' . $class
        ]);
    }
}
