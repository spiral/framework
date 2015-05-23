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
     * Profiling levels.
     */
    const PROFILE_SIMPLE  = 1;
    const PROFILE_EXPLAIN = 2;

    /**
     * ODMManager component.
     *
     * @invisible
     * @var ODM
     */
    protected $odm = null;

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
    protected $config = array(
        'profiling' => self::PROFILE_SIMPLE
    );

    /**
     * Mongo connection instance.
     *
     * @var \Mongo|\MongoClient
     */
    protected $connection = null;

    /**
     * New MongoDatabase instance.
     *
     * @param ODM    $odm    ODMManager component.
     * @param string $name   ODM database instance name/id.
     * @param array  $config Connection configuration.
     */
    public function __construct(ODM $odm, $name, array $config)
    {
        $this->odm = $odm;
        $this->name = $name;
        $this->config = $this->config + $config;

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
     * While profiling enabled driver will create query logging and benchmarking events. This is
     * recommended option on development environment.
     *
     * @param bool $enabled Enable or disable driver profiling.
     * @return static
     */
    public function profiling($enabled = true)
    {
        $this->config['profiling'] = $enabled;

        return $this;
    }

    /**
     * Check if profiling mode is enabled.
     *
     * @return bool
     */
    public function isProfiling()
    {
        return !empty($this->config['profiling']);
    }

    /**
     * Get database profiling level.
     *
     * @return int
     */
    public function getProfilingLevel()
    {
        return $this->config['profiling'];
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
        return new Collection($this->odm, $this->name, $name, $query);
    }
}