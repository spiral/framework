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
use Spiral\Migrations\Operations\Traits\OptionsTrait;

class AddIndex extends IndexOperation
{
    use OptionsTrait;

    /**
     * @param string|null $database
     * @param string      $table
     * @param array       $columns
     * @param array       $options
     */
    public function __construct($database, string $table, array $columns, array $options = [])
    {
        parent::__construct($database, $table, $columns);
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(CapsuleInterface $capsule)
    {
        $schema = $capsule->getSchema($this->getDatabase(), $this->getTable());

        if ($schema->hasIndex($this->columns)) {
            $columns = join(',', $this->columns);
            throw new IndexException(
                "Unable to create index '{$schema->getName()}'.({$columns}), index already exists"
            );
        }

        $schema->index($this->columns)->unique(
            $this->getOption('unique', false)
        );
    }
}