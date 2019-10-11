<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\Migrate;

use Spiral\Database\Database;
use Spiral\Framework\ConsoleTest;

class InitTest extends ConsoleTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->getEnvironment()->set('SAFE_MIGRATIONS', true);
    }

    public function testInit(): void
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);

        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');

        $t = $db->getTables()[0];
        $this->assertSame('migrations', $t->getName());
    }
}
