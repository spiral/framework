<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector\Loaders;

use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\Selector;
use Spiral\Components\ORM\Selector\Loader;

class RootLoader extends Loader
{
    const LOAD_METHOD = Selector::INLOAD;

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

    public function parseRow(array $row)
    {
        //Fetching only required part of resulted row
        $data = $this->fetchData($row);

        if (!$this->checkDuplicate($data))
        {
            $this->result[] = &$data;
            $this->registerReferences($data);
        }

        $this->parseNested($row);
    }
}


