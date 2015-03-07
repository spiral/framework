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

interface MigrationInterface
{
    /**
     * Configuring migration. This method will be automatically called after migration created and used to resolved
     * target database.
     *
     * @param Database $database
     */
    public function setDatabase(Database $database);

    /**
     * Executing migration.
     */
    public function up();

    /**
     * Dropping (rollback) migration.
     */
    public function down();
}