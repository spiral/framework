<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector\Loaders;

use Spiral\Components\DBAL\QueryResult;
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\Selector;
use Spiral\Components\ORM\Selector\Loader;

class PrimaryLoader extends Loader
{
    protected $result = array();

    public function __construct(array $schema, ORM $orm, Loader $parent = null)
    {
        $this->schema = $schema;
        $this->orm = $orm;

        $this->columns = array_keys($this->schema[ORM::E_COLUMNS]);
        $this->countColumns = count($this->columns);

        //No need for aliases
        $this->options['method'] = Selector::INLOAD;

        //Primary table will be named under it's role name by default
        $this->options['tableAlias'] = $schema[ORM::E_ROLE_NAME];
    }

    public function clarifySelector(Selector $selector)
    {
        if (empty($this->loaders))
        {
            //No need to create any aliases
            return;
        }

        parent::clarifySelector($selector);
    }

    protected function clarifyQuery(Selector $selector)
    {
        //Nothing to do
    }

    //TODO: with and without primary key

    public function parseResult(QueryResult $result, &$rowsCount)
    {
        foreach ($result as $row)
        {
            $this->parseRow($row);
            $rowsCount++;
        }

        return $this->result;
    }

    public function parseRow(array $row)
    {
        //Fetching only required part of resulted row
        $data = $this->fetchData($row);

        if (!$this->mountDuplicate($data))
        {
            $this->result[] = &$data;
            $this->registerReferences($data);
        }

        $this->parseNested($row);
    }

    public function addNested($key, $value, $container, array $data)
    {
        if (!isset($this->references[$key . '::' . $value]))
        {
            return;
        }

        $this->references[$key . '::' . $value][$container] = $data;
    }

    public function clean()
    {
        parent::clean();
        $this->result = array();
    }
}


