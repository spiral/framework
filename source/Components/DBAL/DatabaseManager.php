<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

use Spiral\Components\DBAL\Migrations\Repository;
use Spiral\Components\DBAL\Migrations\Migrator;
use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreException;

class DatabaseManager extends Component implements Container\InjectionManagerInterface
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * QueryBuilder constants. This particular constants used in WhereTrait to convert array query
     * to where tokens.
     */
    const TOKEN_AND = "@AND";
    const TOKEN_OR  = "@OR";

    /**
     * By default spiral will force all time conversion into single timezone before storing in
     * database, it will help us to ensure that we have to problems with switching timezones and
     * save a lot of time while development. :)
     */
    const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Container instance.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Constructed instances of DBAL databases.
     *
     * @var Database[]
     */
    protected $databases = [];

    /**
     * DBAL component instance, component is responsible for connections to various SQL databases and
     * their schema builders/describers.
     *
     * @param ConfiguratorInterface $configurator
     * @param Container             $container
     * @throws CoreException
     */
    public function __construct(ConfiguratorInterface $configurator, Container $container)
    {
        $this->config = $configurator->getConfig('dbal');
        $this->container = $container;
    }

    /**
     * Get global timezone name should be used to convert dates and timestamps. Function is static
     * for performance reasons. Right now timezone is hardcoded, but in future we can make it changeable.
     *
     * @return string
     */
    public static function defaultTimezone()
    {
        return static::DEFAULT_TIMEZONE;
    }

    /**
     * Get instance of dbal Database. Database class is high level abstraction at top of Driver.
     * Multiple databases can use same driver and be different by table prefix.
     *
     * @param string $database Internal database name or alias, declared in config.
     * @param array  $config   Forced database configuration.
     * @param Driver $driver   Forced driver instance.
     * @return Database
     * @throws DBALException
     */
    public function db($database = 'default', array $config = [], Driver $driver = null)
    {
        if (isset($this->config['aliases'][$database]))
        {
            $database = $this->config['aliases'][$database];
        }

        if (isset($this->databases[$database]))
        {
            return $this->databases[$database];
        }

        if (empty($config))
        {
            if (!isset($this->config['databases'][$database]))
            {
                throw new DBALException(
                    "Unable to create database, no presets for '{$database}' found."
                );
            }

            $config = $this->config['databases'][$database];
        }

        if (!$driver)
        {
            //Driver identifier can be fetched from connection string
            $driver = substr($config['connection'], 0, strpos($config['connection'], ':'));
            $driver = $this->container->get($this->config['drivers'][$driver], compact('config'));
        }

        benchmark('dbal::database', $database);

        $this->databases[$database] = $this->container->get(
            Database::class,
            [
                'name'        => $database,
                'driver'      => $driver,
                'tablePrefix' => isset($config['tablePrefix']) ? $config['tablePrefix'] : ''
            ],
            null,
            true
        );

        benchmark('dbal::database', $database);

        return $this->databases[$database];
    }

    /**
     * InjectionManager will receive requested class or interface reflection and reflection linked
     * to parameter in constructor or method used to declare dependency.
     *
     * This method can return pre-defined instance or create new one based on requested class, parameter
     * reflection can be used to dynamic class constructing, for example it can define database name
     * or config section should be used to construct requested instance.
     *
     * @param \ReflectionClass     $class
     * @param \ReflectionParameter $parameter
     * @param Container            $container
     * @return mixed
     */
    public function resolveInjection(
        \ReflectionClass $class,
        \ReflectionParameter $parameter,
        Container $container
    )
    {
        return $this->db($parameter->getName());
    }

    /**
     * MigrationRepository instance. Repository responsible for registering and retrieving existed
     * migrations.
     *
     * @param string $directory Directory where migrations should be stored in.
     * @return Repository
     */
    public function migrationRepository($directory = null)
    {
        return Repository::make([
            'directory' => $directory ?: $this->config['migrations']['directory']
        ], $this->container);
    }

    /**
     * Get database specific migrator.
     *
     * @param string $database  Associated database.
     * @param string $directory Directory where migrations should be stored in.
     * @return Migrator
     * @throws CoreException
     */
    public function getMigrator($database = 'default', $directory = null)
    {
        return $this->container->get($this->config['migrations']['migrator'], [
            'database'   => $this->db($database),
            'repository' => $this->migrationRepository($directory),
            'config'     => $this->config['migrations']
        ]);
    }

    /**
     * Helper method used to fill query with binded parameters. This method should NEVER be used to
     * generate database queries and only for debugging.
     *
     * @param string $query      SQL statement with parameter placeholders.
     * @param array  $parameters Parameters to be binded into query.
     * @return mixed
     */
    public static function interpolateQuery($query, array $parameters = [])
    {
        if (empty($parameters))
        {
            return $query;
        }

        array_walk($parameters, function (&$parameter)
        {
            switch (gettype($parameter))
            {
                case "boolean":
                    return $parameter = $parameter ? 'true' : 'false';
                case "integer":
                    return $parameter = $parameter + 0;
                case "NULL":
                    return $parameter = 'NULL';
                case "double":
                    return $parameter = sprintf('%F', $parameter);
                case "string":
                    return $parameter = "'" . addcslashes($parameter, "'") . "'";
                case 'object':
                    if (method_exists($parameter, '__toString'))
                    {
                        return $parameter = "'" . addcslashes((string)$parameter, "'") . "'";
                    }
            }

            return $parameter = "[UNRESOLVED]";
        });

        reset($parameters);
        if (!is_int(key($parameters)))
        {
            return interpolate($query, $parameters, '', '');
        }

        foreach ($parameters as $parameter)
        {
            $query = preg_replace('/\?/', $parameter, $query, 1);
        }

        return $query;
    }
}