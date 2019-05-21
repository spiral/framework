<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);


namespace Spiral\Framework\Migrate;

use Spiral\Command\Cycle\SyncCommand;
use Spiral\Command\Migrate\MigrateCommand;
use Spiral\Command\Migrate\ReplayCommand;
use Spiral\Command\Migrate\RollbackCommand;
use Spiral\Framework\ConsoleTest;
use Symfony\Component\Console\Tester\CommandTester;

class ConfirmTest extends ConsoleTest
{
    public function setUp()
    {
        $this->app = $this->makeApp([
            'SAFE_MIGRATIONS' => false
        ]);
    }

    public function testConfirmMigrate()
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(MigrateCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['n']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertContains('Confirmation', $out);
        $this->assertNotContains('No outstanding', $out);
    }

    public function testConfirmMigrateY()
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(MigrateCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['y']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertContains('Confirmation', $out);
        $this->assertContains('No outstanding', $out);
    }

    public function testConfirmRollbackMigrate()
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(RollbackCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['n']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertContains('Confirmation', $out);
        $this->assertNotContains('No executed', $out);
    }

    public function testConfirmRollbackMigrateY()
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(RollbackCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['y']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertContains('Confirmation', $out);
        $this->assertContains('No executed', $out);
    }

    public function testConfirmReplayMigrate()
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(ReplayCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['n']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertContains('Confirmation', $out);
        $this->assertNotContains('No outstanding', $out);
    }

    public function testConfirmReplayMigrateY()
    {
        $this->runCommandDebug('migrate:init');

        $mc = $this->app->get(ReplayCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['y']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertContains('Confirmation', $out);
        $this->assertContains('No outstanding', $out);
    }

    public function testConfirmCycleSync()
    {
        $mc = $this->app->get(SyncCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['n']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertContains('Confirmation', $out);
        $this->assertNotContains('ORM Schema has been synchronized', $out);
    }

    public function testConfirmCycleSyncY()
    {
        $mc = $this->app->get(SyncCommand::class);
        $mc->setContainer($this->app->getContainer());

        $ct = new CommandTester($mc);
        $ct->setInputs(['y']);
        $ct->execute([]);

        rewind($ct->getOutput()->getStream());
        $out = fread($ct->getOutput()->getStream(), 9000);

        $this->assertContains('Confirmation', $out);
        $this->assertContains('ORM Schema has been synchronized', $out);
    }
}