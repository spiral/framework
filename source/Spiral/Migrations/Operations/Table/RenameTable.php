<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Operations\Table;

use Spiral\Database\Entities\AbstractHandler;
use Spiral\Migrations\CapsuleInterface;
use Spiral\Migrations\Exceptions\Operations\TableException;
use Spiral\Migrations\Operations\TableOperation;

class RenameTable extends TableOperation
{
    /**
     * @var string
     */
    private $newName = '';

    /**
     * @param string|null $database
     * @param string      $table
     * @param string      $newName
     */
    public function __construct($database, string $table, string $newName)
    {
        parent::__construct($database, $table);
        $this->newName = $newName;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(CapsuleInterface $capsule)
    {
        $schema = $capsule->getSchema($this->getDatabase(), $this->getTable());
        $database = $this->database ?? '[default]';

        if (!$schema->exists()) {
            throw new TableException(
                "Unable to rename table '{$database}'.'{$this->getTable()}', table does not exists"
            );
        }

        if ($capsule->getDatabase($this->getDatabase())->hasTable($this->newName)) {
            throw new TableException(
                "Unable to rename table '{$database}'.'{$this->getTable()}', table '{$this->newName}' already exists"
            );
        }

        $schema->setName($this->newName);
        $schema->save(AbstractHandler::DO_ALL);
    }
}