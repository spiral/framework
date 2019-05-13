<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\Cycle;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Transaction;
use Spiral\App\Controller\SelectController;
use Spiral\App\User\User;
use Spiral\App\User\UserRepository;
use Spiral\Boot\FinalizerInterface;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Core\CoreInterface;
use Spiral\Framework\ConsoleTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SchemaTest extends ConsoleTest
{
    public function setUp()
    {
        $this->app = $this->makeApp([
            'SAFE_MIGRATIONS' => true
        ]);
    }

    public function testGetSchema()
    {
        $app = $this->app;
        $app->console()->run('cycle');

        $schema = $app->get(SchemaInterface::class);
        $this->assertSame(User::class, $schema->define('user', Schema::ENTITY));
    }

    public function testMigrate()
    {
        $app = $this->app;
        $this->runCommandDebug('migrate:init', ['-vvv' => true]);

        $output = $this->runCommandDebug('cycle:migrate', ['-r' => true]);
        $this->assertContains('default.users', $output);

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    public function testSync()
    {
        $app = $this->app;
        $output = $app->console()->run('cycle:sync');
        $this->assertContains('default.users', $output->getOutput()->fetch());

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    public function testSyncDebug()
    {
        $output = new BufferedOutput();
        $output->setVerbosity(BufferedOutput::VERBOSITY_VERY_VERBOSE);

        $app = $this->app;
        $app->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'cycle:sync'
        ]), $output);

        $this->assertContains('Begin transaction', $out = $output->fetch());

        $this->assertContains('default.users', $out);
        $this->assertContains('create table', $out);
        $this->assertContains('add column', $out);
        $this->assertContains('add index', $out);

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    /**
     * @expectedException \Cycle\ORM\Exception\ORMException
     */
    public function testSchemaMissing()
    {
        /** @var UserRepository $r */
        $this->app->get(UserRepository::class);
    }

    public function testGetRepository()
    {
        $app = $this->app;
        $this->runCommandDebug('cycle:sync');

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();
        $this->assertSame(1, $u->id);

        /** @var UserRepository $r */
        $r = $app->get(UserRepository::class);
        $this->assertInstanceOf(UserRepository::class, $r);

        // todo: need bugfix
        // $this->assertSame($u, $r->findOne());
    }

    public function testInjectedSelect()
    {
        $app = $this->app;
        $this->runCommandDebug('cycle:sync');

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();
        $this->assertSame(1, $u->id);

        /** @var CoreInterface $c */
        $c = $app->get(CoreInterface::class);
        // $this->assertInstanceOf(UserRepository::class, $r);

        $this->assertSame(1, $c->callAction(
            SelectController::class,
            'select'
        ));
    }

    public function testHeapReset()
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