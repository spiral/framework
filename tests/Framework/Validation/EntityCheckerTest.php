<?php

declare(strict_types=1);

namespace Spiral\Framework\Validation;

use Cycle\ORM\TransactionInterface;
use Spiral\App\User\User;
use Spiral\Database\Database;
use Spiral\Database\DatabaseInterface;
use Spiral\Framework\BaseTest;
use Spiral\Validation\Checker\EntityChecker;
use Spiral\Validation\ValidationInterface;
use Throwable;

class EntityCheckerTest extends BaseTest
{
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

        $this->assertFalse($this->existsValidatorResult(2));
        $this->assertTrue($this->existsValidatorResult(1));
    }

    /**
     * @throws Throwable
     */
    public function testUnique(): void
    {
        /** @var TransactionInterface $transaction */
        $transaction = $this->app->get(TransactionInterface::class);
        $transaction->persist(new User('Valentin'));
        $transaction->persist(new User('Anton'));
        $transaction->run();

        //with/without context
        $this->assertTrue($this->uniqueValidatorResult('John', 'name'));
        $this->assertFalse($this->uniqueValidatorResult('Valentin', 'name'));
        $this->assertTrue($this->uniqueValidatorResult('Valentin', 'name', ['name' => 'Valentin']));

        //additional fields, but they are not in the context
        $this->assertTrue($this->uniqueValidatorResult('Valentin', 'name', ['name' => 'Valentin'], ['id']));

        //With context (and if additional fields without values in the validator)
        $this->assertFalse($this->uniqueValidatorResult('Valentin', 'name', ['name' => 'John']));
        $this->assertFalse($this->uniqueValidatorResult('Valentin', 'name', ['name' => 'John'], ['id']));

        //With additional fields
        $this->assertTrue($this->uniqueValidatorResult(
            'Valentin',
            'name',
            ['name' => 'Valentin'],
            ['id'],
            ['id' => 2]
        ));
        //No match name:Valentin+id:2
        $this->assertTrue($this->uniqueValidatorResult(
            'Valentin',
            'name',
            ['name' => 'John'],
            ['id'],
            ['id' => 2]
        ));
        $this->assertTrue($this->uniqueValidatorResult(
            'Valentin',
            'name',
            ['name' => 'Valentin', 'id' => 2],
            ['id'],
            ['id' => 2] //invalid ID given, but it is not in the context
        ));
        $this->assertTrue($this->uniqueValidatorResult(
            'Valentin',
            'name',
            ['name' => 'Valentin', 'id' => 1],
            ['id'],
            ['id' => 1] //invalid ID given, it is in the context
        ));

        //name:Valentin+id:1 is taken
        $this->assertFalse($this->uniqueValidatorResult(
            'Valentin',
            'name',
            ['name' => 'Valentin'],
            ['id'],
            ['id' => 1]
        ));
        //No match name:John+id:2
        $this->assertFalse($this->uniqueValidatorResult(
            'Valentin',
            'name',
            ['name' => 'John'],
            ['id'],
            ['id' => 1]
        ));
    }

    /**
     * @param int $value
     * @return bool
     */
    private function existsValidatorResult(int $value): bool
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
    private function uniqueValidatorResult(
        string $value,
        string $field,
        array $context = [],
        array $fields = [],
        array $data = []
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
