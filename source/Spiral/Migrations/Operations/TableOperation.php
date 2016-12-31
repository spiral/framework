<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Operations;

use Spiral\Migrations\OperationInterface;

abstract class TableOperation implements OperationInterface
{
    /**
     * @var string|null
     */
    protected $database = null;

    /**
     * @var string
     */
    protected $table;

    /**
     * @param string|null $database
     * @param string      $table
     */
    public function __construct($database, string $table)
    {
        $this->database = $database;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable(): string
    {
        return $this->table;
    }
}