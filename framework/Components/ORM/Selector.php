<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\DBAL\Builders\Common\HavingTrait;
use Spiral\Components\DBAL\Builders\Common\JoinTrait;
use Spiral\Components\DBAL\Builders\Common\WhereTrait;
use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\QueryBuilder;
use Spiral\Components\DBAL\QueryCompiler;
use Spiral\Core\Component;

class Selector extends QueryBuilder
{
    use WhereTrait, JoinTrait, HavingTrait;

    const INLOAD   = 1;
    const POSTLOAD = 1;

    const PRIMARY_MODEL = 0;

    /**
     * Schema of related model.
     *
     * @var array
     */
    protected $schema = array();

    protected $database = null;

    /**
     * ORM component. Used to access related schemas.
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    protected $countColumns = 0;

    protected $mapping = array();

    public $loadWith = array();

    protected $columns = array();

    public function __construct(array $schema, Database $database, ORM $orm, array $query = array())
    {
        $this->schema = $schema;
        $this->database = $database;

        $this->orm = $orm;
    }


    public function with($relation)
    {
        if (strpos($relation, '.') !== false)
        {
            //Building nested relation

        }
        else
        {
            if (!isset($this->schema[ORM::E_RELATIONS][$relation]))
            {
                throw new ORMException("Unknown relation '{$relation}'.");
            }

            $relationSchema = $this->schema[ORM::E_RELATIONS][$relation];
            $relationInstance = $this->orm->getRelation(
                $relationSchema[ORM::R_TYPE],
                $relationSchema[ORM::R_DEFINITION]
            );

            $this->loadWith[$relation] = array(
                'loader'      => $relationInstance::DEFAULT_LOADER,
                'parentTable' => $this->schema[ORM::E_TABLE],
                'relation'    => $relationInstance
            );
        }
    }

    /**
     * Get or render SQL statement.
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    public function sqlStatement(QueryCompiler $compiler = null)
    {
        if (empty($compiler))
        {
            //Database compiler
            $compiler = $this->database->getDriver()->queryCompiler($this->database->getPrefix());
        }

        $this->buildQuery();

        return $compiler->select(
            array($this->schema[ORM::E_TABLE]),
            false, //todo: check if required
            $this->columns,
            $this->joins,
            $this->whereTokens,
            $this->havingTokens,
            array()
        //$this->orderBy,
        //$this->limit,
        //$this->offset
        );
    }

    //    public function sqlStatement()
    //    {
    //
    //        $select = $this->dbalSelect()->from($this->schema[ORM::E_TABLE]);
    //
    //        //Building columns
    //        $select->columns($this->buildColumns());
    //
    //        return $select;
    //    }

    protected function buildQuery()
    {
        $this->countColumns = 0;
        $this->columns = array();
        $this->mapping = array();

        $primaryColumns = false;
        foreach ($this->loadWith as $name => $relation)
        {
            if ($relation['loader'] == self::INLOAD)
            {

                $primaryColumns = true;

                $outerSchema = $this->orm->getSchema($relation['relation']->getTarget());

                $relation['relation']->inload($relation['parentTable'], $this, $this->orm);

                //Adding columns to list
                $this->columns = array_merge(
                    $this->columns,
                    $entityColumns = $this->entityColumns($outerSchema)
                );

                $this->mapping[$name] = array(
                    'columns'  => count($entityColumns),
                    'multiple' => false //FOR RELATION TO DECIDE
                );
            }
        }

        if ($primaryColumns)
        {
            $entityColumns = $this->entityColumns($this->schema);

            $this->mapping = array_merge(array(
                self::PRIMARY_MODEL => array(
                    'columns'  => count($entityColumns),
                    'multiple' => false
                )
            ), $this->mapping);

            $this->columns = array_merge($entityColumns, $this->columns);
        }
        else
        {
            $this->columns = array(
                $this->schema[ORM::E_TABLE] . '.*'
            );
        }
    }

    protected function entityColumns(array $schema)
    {
        $columns = array();
        foreach ($schema[ORM::E_COLUMNS] as $column => $defaultValue)
        {
            $columns[] = $schema[ORM::E_TABLE] . '.' . $column . ' AS c' . (++$this->countColumns);
        }

        return $columns;
    }
}