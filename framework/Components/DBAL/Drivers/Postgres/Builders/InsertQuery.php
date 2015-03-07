<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\Postgres\Builders;

use Spiral\Components\DBAL\Builders\InsertQuery as BaseInsertQuery;
use Spiral\Components\DBAL\DBALException;
use Spiral\Components\DBAL\Drivers\Postgres\PostgresDriver;
use Spiral\Core\Component\LoggerTrait;

class InsertQuery extends BaseInsertQuery
{
    /**
     * Logging.
     */
    use LoggerTrait;

    /**
     * Get or render SQL statement.
     *
     * @return string
     * @throws DBALException
     */
    public function sqlStatement()
    {
        $driver = $this->database->getDriver();
        if (!$driver instanceof PostgresDriver)
        {
            throw new DBALException("Postgres InsertQuery can be used only with Postgres driver.");
        }

        if ($primary = $driver->getPrimary($this->database->getPrefix() . $this->table))
        {
            $this->logger()->debug("Primary key '{sequence}' automatically resolved for table '{table}'.", array(
                'table'    => $this->table,
                'sequence' => $primary
            ));
        }

        return $this->compiler->insert($this->table, $this->columns, $this->parameters, $primary);
    }

    /**
     * Run QueryBuilder statement against parent database. Method will return lastInsertID value.
     *
     * @return mixed
     */
    public function run()
    {
        return (int)$this->database->statement($this->sqlStatement(), $this->getParameters())->fetchColumn();
    }
}