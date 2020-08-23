<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Snapshots;

use PHPUnit\Framework\TestCase;
use Spiral\Snapshots\Snapshot;

class SnapshotTest extends TestCase
{
    public function testSnapshot()
    {
        $e = new \Error("message");
        $s = new Snapshot("id", $e);

        $this->assertSame("id", $s->getID());
        $this->assertSame($e, $s->getException());

        $this->assertStringContainsString("Error", $s->getMessage());
        $this->assertStringContainsString("message", $s->getMessage());
        $this->assertStringContainsString(__FILE__, $s->getMessage());
        $this->assertStringContainsString("21", $s->getMessage());

        $description = $s->describe();
        $this->assertStringContainsString("Error", $description['error']);
        $this->assertStringContainsString("message", $description['error']);
        $this->assertStringContainsString(__FILE__, $description['error']);
        $this->assertStringContainsString("21", $description['error']);

        $this->assertSame(__FILE__, $description['location']['file']);
        $this->assertSame(21, $description['location']['line']);
    }
}
