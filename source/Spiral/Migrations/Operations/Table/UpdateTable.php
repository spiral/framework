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

class UpdateTable extends TableOperation
{
    /**
     * {@inheritdoc}
     */
    public function execute(CapsuleInterface $capsule)
    {
        $schema = $capsule->getSchema($this->getDatabase(), $this->getTable());
        $database = $this->database ?? '[default]';

        if (!$schema->exists()) {
            throw new TableException(
                "Unable to update table '{$database}'.'{$this->getTable()}', no table exists"
            );
        }

        $schema->save(AbstractHandler::DO_ALL);
    }
}