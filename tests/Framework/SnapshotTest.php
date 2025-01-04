<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\Snapshots\SnapshotInterface;
use Spiral\Snapshots\SnapshotterInterface;

final class SnapshotTest extends BaseTestCase
{
    public function testStringConfigParams(): void
    {
        // string important. Emulating string from .env
        $app = $this->makeApp([
            'SNAPSHOT_MAX_FILES' => '1',
            'SNAPSHOT_VERBOSITY' => '1'
        ]);

        self::assertInstanceOf(SnapshotterInterface::class, $app->getContainer()->get(SnapshotterInterface::class));
    }

    public function testSnapshot(): void
    {
        $app = $this->makeApp();

        try {
            throw new \Error('test error');
        } catch (\Error $e) {
            /** @var SnapshotInterface $s */
            $s = $app->getContainer()->get(SnapshotterInterface::class)->register($e);
        }

        self::assertInstanceOf(\Error::class, $s->getException());
    }
}
