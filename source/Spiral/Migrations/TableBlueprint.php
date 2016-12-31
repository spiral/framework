<?php
/**
 * Spiral Framework, Core Components
 *
 * @author    Wolfy-J
 */
namespace Spiral\Migrations;

use Spiral\Database\Schemas\Prototypes\AbstractTable;
use Spiral\Migrations\Operations\Columns\AddColumn;
use Spiral\Migrations\Operations\Columns\AlterColumn;
use Spiral\Migrations\Operations\Columns\DropColumn;
use Spiral\Migrations\Operations\Columns\RenameColumn;
use Spiral\Migrations\Operations\Indexes\AddIndex;
use Spiral\Migrations\Operations\Indexes\AlterIndex;
use Spiral\Migrations\Operations\Indexes\DropIndex;
use Spiral\Migrations\Operations\References\AddReference;
use Spiral\Migrations\Operations\References\AlterReference;
use Spiral\Migrations\Operations\References\DropReference;
use Spiral\Migrations\Operations\Table\CreateTable;
use Spiral\Migrations\Operations\Table\DropTable;
use Spiral\Migrations\Operations\Table\PrimaryKeys;
use Spiral\Migrations\Operations\Table\RenameTable;
use Spiral\Migrations\Operations\Table\UpdateTable;

class TableBlueprint
{
    /**
     * @var CapsuleInterface
     */
    private $capsule = null;

    /**
     * Blueprint specific set of operations.
     *
     * @var array
     */
    private $operations = [];

    /**
     * @var string
     */
    private $table = '';

    /**
     * @var null|string
     */
    private $database = null;

    /**
     * @param CapsuleInterface $capsule
     * @param string           $table
     * @param string|null      $database
     */
    public function __construct(CapsuleInterface $capsule, string $table, string $database = null)
    {
        $this->capsule = $capsule;
        $this->table = $table;
        $this->database = $database;
    }

    /**
     * Get associated table schema.
     *
     * @return AbstractTable
     */
    public function getSchema(): AbstractTable
    {
        return $this->capsule->getSchema($this->table, $this->database);
    }

    /**
     * Example:
     * $table->addColumn('name', 'string', ['length' => 64]);
     * $table->addColumn('status', 'enum', [
     *      'values' => ['active', 'disabled']
     * ]);
     *
     * @param string $name
     * @param string $type
     * @param array  $options
     *
     * @return TableBlueprint
     */
    public function addColumn(string $name, string $type, array $options = []): self
    {
        return $this->addOperation(
            new AddColumn($this->database, $this->table, $name, $type, $options)
        );
    }

    /**
     * Example:
     * $table->alterColumn('name', 'string', ['length' => 128]);
     *
     * @param string $name
     * @param string $type
     * @param array  $options
     *
     * @return TableBlueprint
     */
    public function alterColumn(string $name, string $type, array $options = []): self
    {
        return $this->addOperation(
            new AlterColumn($this->database, $this->table, $name, $type, $options)
        );
    }

    /**
     * Example:
     * $table->renameColumn('column', 'new_name');
     *
     * @param string $name
     * @param string $newName
     *
     * @return TableBlueprint
     */
    public function renameColumn(string $name, string $newName): self
    {
        return $this->addOperation(
            new RenameColumn($this->database, $this->table, $name, $newName)
        );
    }

    /**
     * Example:
     * $table->dropColumn('email');
     *
     * @param string $name
     *
     * @return TableBlueprint
     */
    public function dropColumn(string $name): self
    {
        return $this->addOperation(
            new DropColumn($this->database, $this->table, $name)
        );
    }

    /**
     * Example:
     * $table->addIndex(['email'], ['unique' => true]);
     *
     * @param array $columns
     * @param array $options
     *
     * @return TableBlueprint
     */
    public function addIndex(array $columns, array $options = []): self
    {
        return $this->addOperation(
            new AddIndex($this->database, $this->table, $columns, $options)
        );
    }

    /**
     * Example:
     * $table->alterIndex(['email'], ['unique' => false]);
     *
     * @param array $columns
     * @param array $options
     *
     * @return TableBlueprint
     */
    public function alterIndex(array $columns, array $options): self
    {
        return $this->addOperation(
            new AlterIndex($this->database, $this->table, $columns, $options)
        );
    }

    /**
     * Example:
     * $table->dropIndex(['email']);
     *
     * @param array $columns
     *
     * @return TableBlueprint
     */
    public function dropIndex(array $columns): self
    {
        return $this->addOperation(
            new DropIndex($this->database, $this->table, $columns)
        );
    }

    /**
     * Example:
     * $table->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE']);
     *
     * @param string $column
     * @param string $foreignTable Database isolation prefix will be automatically added.
     * @param string $foreignKey
     * @param array  $options
     *
     * @return TableBlueprint
     */
    public function addForeignKey(
        string $column,
        string $foreignTable,
        string $foreignKey,
        array $options = []
    ): self {
        return $this->addOperation(
            new AddReference(
                $this->database,
                $this->table,
                $column,
                $foreignTable,
                $foreignKey,
                $options
            )
        );
    }


    /**
     * Example:
     * $table->alterForeignKey('user_id', 'users', 'id', ['delete' => 'NO ACTION']);
     *
     * @param string $column
     * @param string $foreignTable
     * @param string $foreignKey
     * @param array  $options
     *
     * @return TableBlueprint
     */
    public function alterForeignKey(
        string $column,
        string $foreignTable,
        string $foreignKey,
        array $options = []
    ): self {
        return $this->addOperation(
            new AlterReference(
                $this->database,
                $this->table,
                $column,
                $foreignTable,
                $foreignKey,
                $options
            )
        );
    }

    /**
     * Example:
     * $table->dropForeignKey('user_id');
     *
     * @param string $column
     *
     * @return TableBlueprint
     */
    public function dropForeignKey(string $column): self
    {
        return $this->addOperation(
            new DropReference($this->database, $this->table, $column)
        );
    }

    /**
     * Set table primary keys index. Attention, you can only call it when table being created.
     *
     * @param array $keys
     *
     * @return TableBlueprint
     */
    public function setPrimaryKeys(array $keys): self
    {
        return $this->addOperation(
            new PrimaryKeys($this->database, $this->table, $keys)
        );
    }

    /**
     * Create table schema.
     */
    public function create()
    {
        $this->addOperation(
            new CreateTable($this->database, $this->table)
        );

        $this->execute();
    }

    /**
     * Update table schema.
     */
    public function update()
    {
        $this->addOperation(
            new UpdateTable($this->database, $this->table)
        );

        $this->execute();
    }

    /**
     * Drop table.
     */
    public function drop()
    {
        $this->addOperation(
            new DropTable($this->database, $this->table)
        );

        $this->execute();
    }

    /**
     * Rename table.
     *
     * @param string $newName
     */
    public function rename(string $newName)
    {
        $this->addOperation(
            new RenameTable($this->database, $this->table, $newName)
        );

        $this->execute();
    }

    /**
     * Register new operation.
     *
     * @param OperationInterface $operation
     *
     * @return TableBlueprint
     */
    public function addOperation(OperationInterface $operation): self
    {
        $this->operations[] = $operation;

        return $this;
    }

    /**
     * Execute blueprint operations.
     */
    private function execute()
    {
        $this->capsule->execute($this->operations);
    }
}