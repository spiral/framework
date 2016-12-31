<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Operations\Table;

use Spiral\Migrations\CapsuleInterface;
use Spiral\Migrations\Exceptions\Operations\TableException;
use Spiral\Migrations\Operations\TableOperation;

class PrimaryKeys extends TableOperation
{
    /**
     * @var array
     */
    private $columns = [];

    /**
     * @param string|null $database
     * @param string      $table
     * @param array       $columns
     */
    public function __construct($database, string $table, array $columns)
    {
        parent::__construct($database, $table);
        $this->columns = $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(CapsuleInterface $capsule)
    {
        $schema = $capsule->getSchema($this->getDatabase(), $this->getTable());
        $database = $this->database ?? '[default]';

        if ($schema->exists()) {
            throw new TableException(
                "Unable to set primary keys for table '{$database}'.'{$this->getTable()}', table already exists"
            );
        }

        $schema->setPrimaryKeys($this->columns);
    }
}