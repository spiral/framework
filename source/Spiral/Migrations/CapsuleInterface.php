<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations;

use Spiral\Database\Entities\Database;
use Spiral\Database\Entities\Table;
use Spiral\Database\Schemas\Prototypes\AbstractTable;
use Spiral\Migrations\Exceptions\ContextException;

/**
 * Migration capsule (isolation).
 */
interface CapsuleInterface
{
    /**
     * @param string $database
     *
     * @return Database
     */
    public function getDatabase(string $database = null): Database;

    /**
     * @param string|null $database
     * @param string      $table
     *
     * @return Table
     */
    public function getTable($database, string $table): Table;

    /**
     * Get schema associated with given database and table.
     *
     * @param string|null $database
     * @param string      $table
     *
     * @return AbstractTable
     *
     * @throws ContextException
     */
    public function getSchema($database, string $table): AbstractTable;

    /**
     * Execute given set of operations.
     *
     * @param OperationInterface[] $operations
     */
    public function execute(array $operations);
}