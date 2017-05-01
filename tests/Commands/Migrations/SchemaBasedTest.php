<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Migrations;

use Spiral\Migrations\Configs\MigrationsConfig;
use Spiral\Tests\BaseTest;

class SchemaBasedTest extends BaseTest
{
    public function testCreateMigrations()
    {
        /** @var MigrationsConfig $mConfig */
        $mConfig = $this->container->get(MigrationsConfig::class);

        $this->files->ensureDirectory($mConfig->getDirectory());
        $this->assertEmpty($this->files->getFiles($mConfig->getDirectory()));

        $this->commands->run('orm:schema', [
            '--migrate' => true
        ]);

        $this->assertNotEmpty($this->files->getFiles($mConfig->getDirectory()));

        $this->assertEmpty($this->db->getTables());

        $this->assertContains('not configured yet', $this->commands->run('migrate:status')->getOutput()->fetch());

        $this->assertSame(0, $this->commands->run('migrate:init')->getCode());
        $this->assertCount(1, $this->db->getTables());

        $this->assertSame(0, $this->commands->run('migrate')->getCode());
        $this->assertCount(2, $this->db->getTables());

        $this->assertContains('Migration', $this->commands->run('migrate:status')->getOutput()->fetch());

        $this->assertSame(0, $this->commands->run('migrate:rollback')->getCode());
        $this->assertCount(1, $this->db->getTables());

        $this->assertSame(0, $this->commands->run('migrate:replay')->getCode());
        $this->assertCount(2, $this->db->getTables());
    }
}