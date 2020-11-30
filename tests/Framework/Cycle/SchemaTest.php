<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Cycle;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Transaction;
use Spiral\App\Controller\SelectController;
use Spiral\App\TestApp;
use Spiral\App\User\User;
use Spiral\App\User\UserRepository;
use Spiral\Boot\Environment;
use Spiral\Boot\FinalizerInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Core\CoreInterface;
use Spiral\Tests\Framework\ConsoleTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SchemaTest extends ConsoleTest
{
    public function setUp(): void
    {
        $this->app = $this->makeApp([
            'SAFE_MIGRATIONS' => true
        ]);
    }

    public function testGetSchema(): void
    {
        $app = $this->app;
        $app->console()->run('cycle');

        /** @var SchemaInterface $schema */
        $schema = $app->get(SchemaInterface::class);
        $this->assertSame(User::class, $schema->define('user', Schema::ENTITY));
    }

    public function testRegenerateSchema(): void
    {
        $app = $this->app;

        /** @var SchemaInterface $schema */
        $schema = $app->get(SchemaInterface::class);

        $this->assertTrue($schema->defines('user'));
        $this->assertSame(User::class, $schema->define('user', Schema::ENTITY));
    }

    public function testRegenerateEmptySchema(): void
    {
        $app = TestApp::init(
            [
                'root'    => __DIR__ . '/../../..',
                'app'     => __DIR__ . '/../../emptyApp',
                'runtime' => sys_get_temp_dir() . '/spiral',
                'cache'   => sys_get_temp_dir() . '/spiral',
            ],
            new Environment(['SAFE_MIGRATIONS' => true]),
            false
        );

        $app->getContainer()->bind(
            MemoryInterface::class,
            new TrackedMemory($app->get(MemoryInterface::class))
        );
        /** @var TrackedMemory $memory */
        $memory = $app->get(MemoryInterface::class);

        //Emulate multiple re-generations for empty schemas
        $app->get(SchemaInterface::class);
        $app->get(SchemaInterface::class);
        $app->get(SchemaInterface::class);

        /** @var SchemaInterface $schema */
        $schema = $app->get(SchemaInterface::class);
        $this->assertSame(1, $memory->saveCount);
        $this->assertFalse($schema->defines('user'));
    }

    public function testMigrate(): void
    {
        $app = $this->app;
        $this->runCommandDebug('migrate:init', ['-vvv' => true]);

        $output = $this->runCommandDebug('cycle:migrate', ['-r' => true]);
        $this->assertStringContainsString('default.users', $output);

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    public function testSync(): void
    {
        $output = $this->runCommand('cycle:sync');
        $this->assertStringContainsString('default.users', $output);

        $u = new User('Antony');
        $this->app->get(Transaction::class)->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    public function testSyncDebug(): void
    {
        $output = new BufferedOutput();
        $output->setVerbosity(BufferedOutput::VERBOSITY_VERY_VERBOSE);

        $app = $this->app;
        $app->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'cycle:sync'
        ]), $output);

        $this->assertStringContainsString('Begin transaction', $out = $output->fetch());

        $this->assertStringContainsString('default.users', $out);
        $this->assertStringContainsString('create table', $out);
        $this->assertStringContainsString('add column', $out);
        $this->assertStringContainsString('add index', $out);

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    public function testGetRepository(): void
    {
        $app = $this->app;
        $this->runCommandDebug('cycle:sync');

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();
        $this->assertSame(1, $u->id);

        /** @var UserRepository $r */
        $r = $app->get(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $r);
        $this->assertSame($u, $r->findOne());
    }

    public function testInjectedSelect(): void
    {
        $app = $this->app;
        $this->runCommandDebug('cycle:sync');

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();
        $this->assertSame(1, $u->id);

        /** @var CoreInterface $c */
        $c = $app->get(CoreInterface::class);

        $this->assertSame(1, $c->callAction(
            SelectController::class,
            'select'
        ));
    }

    public function testHeapReset(): void
    {
        $app = $this->app;
        $this->runCommandDebug('cycle:sync');

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();
        $this->assertSame(1, $u->id);

        /** @var ORMInterface $orm */
        $orm = $app->get(ORMInterface::class);

        $heap = $orm->getHeap();
        $this->assertTrue($heap->has($u));

        $this->app->get(FinalizerInterface::class)->finalize();

        $this->assertFalse($heap->has($u));
    }
}
