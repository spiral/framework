<?php

declare(strict_types=1);

namespace Spiral\Tests\Snapshots;

use PHPUnit\Framework\TestCase;
use Spiral\Snapshots\Snapshot;

class SnapshotTest extends TestCase
{
    public function testSnapshot(): void
    {
        $e = new \Error("message");
        $s = new Snapshot("id", $e);

        self::assertSame("id", $s->getID());
        self::assertSame($e, $s->getException());

        self::assertStringContainsString("Error", $s->getMessage());
        self::assertStringContainsString("message", $s->getMessage());
        self::assertStringContainsString(__FILE__, $s->getMessage());
        self::assertStringContainsString("14", $s->getMessage());

        $description = $s->describe();
        self::assertStringContainsString("Error", $description['error']);
        self::assertStringContainsString("message", $description['error']);
        self::assertStringContainsString(__FILE__, $description['error']);
        self::assertStringContainsString("14", $description['error']);

        self::assertSame(__FILE__, $description['location']['file']);
        self::assertSame(14, $description['location']['line']);
    }
}
