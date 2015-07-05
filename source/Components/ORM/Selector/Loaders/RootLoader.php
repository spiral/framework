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
    /**
     * RootLoader always work via INLOAD.
     */
    const LOAD_METHOD = Selector::INLOAD;

    /**
     * New instance of ORM Loader. Loader can always load additional components using
     * ORM->getContainer().
     *
     * For root loader model schema should proved as loader definition.
     *
     * @param ORM    $orm
     * @param string $container  Location in parent loaded where data should be attached.
     * @param array  $definition Definition compiled by relation relation schema and stored in ORM
     *                           cache.
     * @param Loader $parent     Parent loader if presented.
     */
    public function __construct(ORM $orm, $container, array $definition = [], Loader $parent = null)
    {
        $this->orm = $orm;
        $this->schema = $definition;

        //No need for aliases
        $this->options['method'] = Selector::INLOAD;

        //Primary table will be named under it's declared table name by default (without prefix)
        $this->options['alias'] = $this->schema[ORM::E_TABLE];

        $this->columns = array_keys($this->schema[ORM::E_COLUMNS]);
    }

    /**
     * Configure selector options.
     *
     * @param Selector $selector
     */
    public function configureSelector(Selector $selector)
    {
        if (empty($this->loaders))
        {
            //No need to create any aliases
            return;
        }

        parent::configureSelector($selector);
    }

    /**
     * ORM Loader specific method used to clarify selector conditions, join and columns with
     * loader specific information.
     *
     * @param Selector $selector
     */
    protected function clarifySelector(Selector $selector)
    {
        //Nothing to do
    }

    /**
     * Parse single result row, should fetch related model fields and run nested loader parsers.
     *
     * @param array $row
     * @return mixed
     */
    public function parseRow(array $row)
    {
        //Fetching only required part of resulted row
        $data = $this->fetchData($row);
        if ($this->deduplicate($data))
        {
            //Yes, this is reference, i'm using this method to build data tree using nested parsers
            $this->result[] = &$data;
            $this->collectReferences($data);
        }

        $this->parseNested($row);
    }
}


