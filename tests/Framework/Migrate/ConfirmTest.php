<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Migrate;

use Spiral\Command\Migrate\MigrateCommand;
use Spiral\Command\Migrate\ReplayCommand;
use Spiral\Command\Migrate\RollbackCommand;
use Spiral\Framework\ConsoleTest;
use Symfony\Component\Console\Tester\CommandTester;

class ConfirmTest extends ConsoleTest
{
    public function setUp(): void
    {
        $this->app = $this->makeApp([
            'SAFE_MIGRATIONS' => false
        ]);
    }

    public function testConfirmMigrate(): void
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(MigrateCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['n']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertStringContainsString('Confirmation', $out);
        $this->assertStringNotContainsString('No outstanding', $out);
    }

    public function testConfirmMigrateY(): void
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(MigrateCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['y']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertStringContainsString('Confirmation', $out);
        $this->assertStringContainsString('No outstanding', $out);
    }

    public function testConfirmRollbackMigrate(): void
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(RollbackCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['n']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertStringContainsString('Confirmation', $out);
        $this->assertStringNotContainsString('No executed', $out);
    }

    public function testConfirmRollbackMigrateY(): void
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(RollbackCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['y']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertStringContainsString('Confirmation', $out);
        $this->assertStringContainsString('No executed', $out);
    }

    public function testConfirmReplayMigrate(): void
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(ReplayCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['n']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertStringContainsString('Confirmation', $out);
        $this->assertStringNotContainsString('No outstanding', $out);
    }

    public function testConfirmReplayMigrateY(): void
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(ReplayCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['y']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertStringContainsString('Confirmation', $out);
        $this->assertStringContainsString('No outstanding', $out);
    }
}
