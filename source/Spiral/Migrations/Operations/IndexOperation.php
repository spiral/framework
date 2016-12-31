<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Operations;

abstract class IndexOperation extends TableOperation
{
    /**
     * Columns index associated to, order matter!
     *
     * @var array
     */
    protected $columns = [];

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
}