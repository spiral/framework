<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Validation;

use Cycle\ORM\TransactionInterface;
use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Spiral\App\TestApp;
use Spiral\App\User\User;
use Spiral\Tests\Framework\BaseTest;
use Spiral\Validation\ValidationInterface;
use Throwable;

class EntityCheckerTest extends BaseTest
{
    /** @var TestApp */
    private $app;

    public function setUp(): void
    {
        $this->app = $this->makeApp();

        /** @var Database $database */
        $database = $this->app->get(DatabaseInterface::class);

        $table = $database->table('users')->getSchema();
        $table->primary('id');
        $table->string('name');
        $table->save();
    }

    /**
     * @throws Throwable
     */
    public function testExistsByPK(): void
    {
        /** @var TransactionInterface $transaction */
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist(new User('Valentin'));
        $transaction->run();

        $this->assertFalse($this->exists(2));
        $this->assertTrue($this->exists(1));
    }

    public function testCaseInsensitiveExists(): void
    {
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist(new User('Valentin'));
        $transaction->run();

        $this->assertTrue($this->exists('vALenTIn', 'name', true));
        $this->assertFalse($this->exists('valentin', 'name', false));
    }

    /**
     * @throws Throwable
     */
    public function testExistsByField(): void
    {
        /** @var TransactionInterface $transaction */
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist(new User('Valentin'));
        $transaction->run();

        $this->assertFalse($this->exists('John', 'name'));
        $this->assertTrue($this->exists('Valentin', 'name'));
    }

    /**
     * @throws Throwable
     */
    public function testSimpleUnique(): void
    {
        /** @var TransactionInterface $transaction */
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist(new User('Valentin'));
        $transaction->persist(new User('Anton'));
        $transaction->run();

        $this->assertTrue($this->isUnique('John', 'name'));
        $this->assertFalse($this->isUnique('Valentin', 'name'));
    }

    public function testCaseInsensitiveUnique(): void
    {
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist(new User('Valentin'));
        $transaction->run();

        $this->assertFalse($this->isUnique('vaLeNtIN', 'name', [], null, [], true));
        $this->assertFalse($this->isUnique('1', 'id', ['name' => 'valEntIn'], null, ['name'], true));
        $this->assertTrue($this->isUnique('vaLeNtIN', 'name'));
        $this->assertTrue($this->isUnique('1', 'id', ['name' => 'valEntIn'], null, ['name']));
    }

    /**
     * @throws Throwable
     */
    public function testContextualUnique(): void
    {
        $user1 = new User('Valentin');
        $user2 = new User('Anton');
        $user3 = new User('John');

        /** @var TransactionInterface $transaction */
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist($user1);
        $transaction->persist($user2);
        $transaction->persist($user3);
        $transaction->run();

        //context match
        $this->assertTrue($this->isUnique('Valentin', 'name', [], $user1));
        $this->assertTrue($this->isUnique('Valentin', 'name', [], $user1, ['id']));
        $this->assertTrue($this->isUnique('Valentin', 'name', ['id' => 1], $user1, ['id']));

        //context mismatch, unique in db
        $this->assertTrue($this->isUnique('Valentin', 'name', ['id' => 2], $user1, ['id']));
        $this->assertTrue($this->isUnique('Valentin', 'name', ['id' => 2], $user3, ['id']));

        //context mismatch, not unique in db
        $this->assertFalse($this->isUnique('Valentin', 'name', [], $user2));
        $this->assertFalse($this->isUnique('Valentin', 'name', [], $user2, ['id']));
    }

    /**
     * @param mixed       $value
     * @param string|null $field
     * @param bool        $ignoreCase
     * @return bool
     */
    private function exists($value, ?string $field = null, bool $ignoreCase = false): bool
    {
        /** @var ValidationInterface $validator */
        $validator = $this->app->get(ValidationInterface::class);
        $validator = $validator->validate(
            ['value' => $value],
            ['value' => [['entity::exists', User::class, $field, $ignoreCase]]]
        );

        return $validator->isValid();
    }

    /**
     * @param string      $value
     * @param string      $field
     * @param array       $data
     * @param object|null $context
     * @param string[]    $fields
     * @param bool        $ignoreCase
     * @return bool
     */
    private function isUnique(
        string $value,
        string $field,
        array $data = [],
        ?object $context = null,
        array $fields = [],
        bool $ignoreCase = false
    ): bool {
        /** @var ValidationInterface $validator */
        $validator = $this->app->get(ValidationInterface::class);
        $validator = $validator->validate(
            ['value' => $value] + $data,
            ['value' => [['entity::unique', User::class, $field, $fields, $ignoreCase]]]
        );
        if ($context !== null) {
            $validator = $validator->withContext($context);
        }

        return $validator->isValid();
    }
}
