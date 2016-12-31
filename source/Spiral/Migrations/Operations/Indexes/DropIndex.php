<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Operations\Indexes;

use Spiral\Migrations\CapsuleInterface;
use Spiral\Migrations\Exceptions\Operations\IndexException;
use Spiral\Migrations\Operations\IndexOperation;

class DropIndex extends IndexOperation
{
    /**
     * {@inheritdoc}
     */
    public function execute(CapsuleInterface $capsule)
    {
        $schema = $capsule->getSchema($this->getDatabase(), $this->getTable());

        if (!$schema->hasIndex($this->columns)) {
            $columns = join(',', $this->columns);
            throw new IndexException(
                "Unable to drop index '{$schema->getName()}'.({$columns}), index does not exists"
            );
        }

        $schema->dropIndex($this->columns);
    }
}