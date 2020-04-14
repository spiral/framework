<?php

declare(strict_types=1);

namespace Spiral\Framework\Validation;

use Cycle\ORM\TransactionInterface;
use Spiral\App\TestApp;
use Spiral\App\User\User;
use Spiral\Database\Database;
use Spiral\Database\DatabaseInterface;
use Spiral\Framework\BaseTest;
use Spiral\Validation\Checker\EntityChecker;
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
    public function testExists(): void
    {
        /** @var TransactionInterface $transaction */
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist(new User('Valentin'));
        $transaction->run();

        $this->assertFalse($this->exists(2));
        $this->assertTrue($this->exists(1));
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

    /**
     * @throws Throwable
     */
    public function testContextualUnique(): void
    {
        /** @var TransactionInterface $transaction */
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist(new User('Valentin'));
        $transaction->persist(new User('Anton'));
        $transaction->run();

        //context match
        $this->assertTrue($this->isUnique('Valentin', 'name', [], ['name' => 'Valentin']));
        $this->assertTrue($this->isUnique('Valentin', 'name', [], ['name' => 'Valentin'], ['id']));
        $this->assertTrue($this->isUnique('Valentin', 'name', ['id' => 2], ['id' => 2, 'name' => 'Valentin'], ['id']));
        $this->assertTrue($this->isUnique('Valentin', 'name', ['id' => 1], ['id' => 1, 'name' => 'Valentin'], ['id']));

        //context mismatch, unique in db
        $this->assertTrue($this->isUnique('Valentin', 'name', ['id' => 2], ['name' => 'Valentin'], ['id']));
        //context mismatch, not unique in db
        $this->assertFalse($this->isUnique('Valentin', 'name', ['id' => 1], ['name' => 'Valentin'], ['id']));
        $this->assertFalse($this->isUnique('Valentin', 'name', [], ['name' => 'John']));
        $this->assertFalse($this->isUnique('Valentin', 'name', [], ['name' => 'John'], ['id']));
    }

    /**
     * @param int $value
     * @return bool
     */
    private function exists(int $value): bool
    {
        /** @var ValidationInterface $validator */
        $validator = $this->app->get(ValidationInterface::class);
        $validator = $validator->validate(
            ['value' => $value],
            ['value' => [['entity::exists', User::class]]]
        );

        return $validator->isValid();
    }

    /**
     * @param string   $value
     * @param string   $field
     * @param array    $context
     * @param string[] $fields
     * @param array    $data
     * @return bool
     */
    private function isUnique(
        string $value,
        string $field,
        array $data = [],
        array $context = [],
        array $fields = []
    ): bool {
        /** @var ValidationInterface $validator */
        $validator = $this->app->get(ValidationInterface::class);
        $validator = $validator->validate(
            ['value' => $value] + $data,
            ['value' => [['entity::unique', User::class, $field, $fields]]]
        )->withContext([EntityChecker::class => $context]);

        return $validator->isValid();
    }
}
