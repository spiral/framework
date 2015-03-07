<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Migrations;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\Schemas\BaseTableSchema;
use Spiral\Components\DBAL\Table;
use Spiral\Core\Component;

abstract class Migration extends Component implements MigrationInterface
{
    /**
     * Target database instance.
     *
     * @var Database
     */
    protected $database = null;

    /**
     * Configuring migration. This method will be automatically called after migration created and used to resolved
     * target database.
     *
     * @param Database $database
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Get Table instance associated with target database.
     *
     * @param string $name Table name without prefix.
     * @return Table
     */
    public function table($name)
    {
        return $this->database->table($name);
    }

    /**
     * Get table schema from associated database, schema can be used for different operations, such as creation, updating,
     * dropping and etc.
     *
     * @param string $table Table name without prefix.
     * @return BaseTableSchema
     */
    public function schema($table)
    {
        return $this->table($table)->schema();
    }

    /**
     * Executing migration.
     */
    abstract public function up();

    /**
     * Dropping (rollback) migration.
     */
    abstract public function down();
}