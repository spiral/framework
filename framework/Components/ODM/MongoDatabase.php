<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM;

use Spiral\Core\Container\InjectableInterface;

class MongoDatabase extends \MongoDB implements InjectableInterface
{
    /**
     * InjectableInterface declares to spiral Container that requested interface or class should
     * not be resolved using default mechanism. Following interface does not require any methods,
     * however class or other interface which inherits InjectableInterface should declare constant
     * named "INJECTION_MANAGER" with name of class responsible for resolving that injection.
     *
     * InjectionFactory will receive requested class or interface reflection and reflection linked
     * to parameter in constructor or method used to declare injection.
     */
    const INJECTION_MANAGER = 'Spiral\Components\ODM\ODM';

    /**
     * ODM database instance name/id.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Connection configuration.
     *
     * @var array
     */
    protected $config = array();

    /**
     * ODMManager component.
     *
     * @invisible
     * @var ODM
     */
    protected $odm = null;

    /**
     * Mongo connection instance.
     *
     * @var \Mongo|\MongoClient
     */
    protected $connection = null;

    /**
     * New MongoDatabase instance.
     *
     * @param string $name   ODM database instance name/id.
     * @param array  $config Connection configuration.
     * @param ODM    $odm    ODMManager component.
     */
    public function __construct($name, array $config, ODM $odm)
    {
        $this->name = $name;
        $this->config = $config;
        $this->odm = $odm;

        //Selecting client
        benchmark('mongo::connect', $this->config['database']);
        if (class_exists('MongoClient', false))
        {
            $this->connection = new \MongoClient($this->config['server'], $this->config['options']);
        }
        else
        {
            $this->connection = new \Mongo($this->config['server'], $this->config['options']);
        }

        parent::__construct($this->connection, $this->config['database']);
        benchmark('mongo::connect', $this->config['database']);
    }

    /**
     * Internal database name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * ODM collection instance for current db. ODMCollection has all the featured from MongoCollection,
     * but it will resolve results as ODM Document.
     *
     * @param string $name  Collection name.
     * @param array  $query Initial collection query.
     * @return Collection
     */
    public function odmCollection($name, array $query = array())
    {
        return Collection::make(array(
            'name'     => $name,
            'database' => $this->name,
            'odm'      => $this->odm,
            'query'    => $query
        ));
    }
}