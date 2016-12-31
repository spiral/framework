<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations;

use Spiral\Database\DatabaseManager;
use Spiral\Database\Entities\Database;
use Spiral\Database\Entities\Table;
use Spiral\Database\Schemas\Prototypes\AbstractTable;
use Spiral\Migrations\Exceptions\CapsuleException;

/**
 * Isolates set of table specific operations and schemas into one place. Kinda repository.
 */
class MigrationCapsule implements CapsuleInterface
{
    /**
     * Cached set of table schemas.
     *
     * @var array
     */
    private $schemas = [];

    /**
     * @invisible
     * @var DatabaseManager
     */
    protected $dbal = null;

    /**
     * @param DatabaseManager $dbal
     */
    public function __construct(DatabaseManager $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(string $database = null): Database
    {
        return $this->dbal->database($database);
    }

    /**
     * {@inheritdoc}
     */
    public function getTable($database, string $table): Table
    {
        return $this->dbal->database($database)->table($table);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema($database, string $table): AbstractTable
    {
        if (!isset($this->schemas[$database . '.' . $table])) {
            $schema = $this->getTable($database, $table)->getSchema();

            //We have to declare existed to prevent dropping existed schema
            $this->schemas[$database . '.' . $table] = $schema;
        }

        return $this->schemas[$database . '.' . $table];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function execute(array $operations)
    {
        /*
         * Executing operation per operation.
         */
        foreach ($operations as $operation) {
            if ($operation instanceof OperationInterface) {
                $operation->execute($this);
            } else {
                throw new CapsuleException(sprintf(
                    "Migration operation expected to be an instance of OperationInterface, '%s' given",
                    get_class($operation)
                ));
            }
        }
    }
}