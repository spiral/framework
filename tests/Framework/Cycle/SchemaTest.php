<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\Cycle;

use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Transaction;
use Spiral\App\User\User;
use Spiral\App\User\UserRepository;
use Spiral\Framework\BaseTest;

class SchemaTest extends BaseTest
{
    public function testGetSchema()
    {
        $app = $this->makeApp();
        $app->console()->run('cycle');

        $schema = $app->get(SchemaInterface::class);
        $this->assertSame(User::class, $schema->define('user', Schema::ENTITY));
    }

    public function testMigrate()
    {
        $app = $this->makeApp();
        $app->console()->run('migrate:init', ['-vvv' => true]);

        $output = $app->console()->run('cycle:migrate', ['-r' => true]);
        $this->assertContains('default.users', $output->getOutput()->fetch());

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    public function testSync()
    {
        $app = $this->makeApp();
        $output = $app->console()->run('cycle:sync');
        $this->assertContains('default.users', $output->getOutput()->fetch());

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    public function testGetRepository()
    {
        $app = $this->makeApp();
        $app->console()->run('cycle:sync');

        $u = new User('Antony');
        $app->get(Transaction::class)->persist($u)->run();
        $this->assertSame(1, $u->id);

        /** @var UserRepository $r */
        $r = $app->get(UserRepository::class);
        $this->assertInstanceOf(UserRepository::class, $r);

        // todo: need bugfix
        // $this->assertSame($u, $r->findOne());
    }
}