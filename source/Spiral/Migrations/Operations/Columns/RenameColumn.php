<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Operations\Columns;

use Spiral\Migrations\CapsuleInterface;
use Spiral\Migrations\Exceptions\Operations\ColumnException;
use Spiral\Migrations\Operations\TableOperation;

class RenameColumn extends TableOperation
{
    /**
     * Column name.
     *
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $newName = '';

    /**
     * @param string $database
     * @param string $table
     * @param string $name
     * @param string $newName
     */
    public function __construct($database, string $table, string $name, string $newName)
    {
        parent::__construct($database, $table);

        $this->name = $name;
        $this->newName = $newName;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(CapsuleInterface $capsule)
    {
        $schema = $capsule->getSchema($this->getDatabase(), $this->getTable());

        if (!$schema->hasColumn($this->name)) {
            throw new ColumnException(
                "Unable to drop column '{$schema->getName()}'.'{$this->name}', column does not exists"
            );
        }

        if ($schema->hasColumn($this->newName)) {
            throw new ColumnException(
                "Unable to rename column '{$schema->getName()}'.'{$this->name}', column '{$this->newName}' already exists"
            );
        }

        //Declaring column
        $schema->renameColumn($this->name, $this->newName);
    }
}