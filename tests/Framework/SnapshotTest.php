<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\Snapshots\SnapshotInterface;
use Spiral\Snapshots\SnapshotterInterface;

class SnapshotTest extends BaseTest
{
    protected function refreshApp(): void {}

    public function testStringConfigParams()
    {
        // string important. Emulating string from .env
        $this->app = $this->initApp([
            'SNAPSHOT_MAX_FILES' => '1',
            'SNAPSHOT_VERBOSITY' => '1'
        ]);

        $this->assertInstanceOf(SnapshotterInterface::class, $app->get(SnapshotterInterface::class));
    }

    public function testSnapshot(): void
    {
        $app = $this->makeApp();

        try {
            throw new \Error('test error');
        } catch (\Error $e) {
            /** @var SnapshotInterface $s */
            $s = $app->get(SnapshotterInterface::class)->register($e);
        }

        $this->assertInstanceOf(\Error::class, $s->getException());
    }
}
