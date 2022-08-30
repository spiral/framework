<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Migrate;

use Cycle\Database\Database;
use Spiral\Tests\Framework\ConsoleTest;

class InitTest extends ConsoleTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app = $this->makeApp([
            'SAFE_MIGRATIONS' => true
        ]);
    }

    public function testInit(): void
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);

        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');

        // Behaviour https://github.com/spiral/migrations/releases/tag/v2.3.0
        $this->assertCount(0, $db->getTables());
    }
}
