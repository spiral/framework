<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

use Spiral\Core\Component;

abstract class QueryBuilder extends Component implements SqlFragmentInterface
{
    /**
     * Database generated query has to be performed against, output result is depends on specific
     * builder implementation.
     *
     * @invisible
     * @var Database
     */
    protected $database = null;

    /**
     * QueryCompiler is low level SQL compiler which used by different query builders to generate
     * statement based on provided tokens. Every builder will get it's own QueryCompiler at it has
     * some internal isolation features (such as query specific table aliases).
     *
     * @invisible
     * @var QueryCompiler
     */
    protected $compiler = null;

    /**
     * Binded query parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * QueryBuilder class is parent for all existed DBAL query builders. Every QueryBuilder will have
     * attached QueryGrammar instance provided by driver and responsible for building queries based
     * on provided tokens. Additionally QueryBuilder have common mechanism to register query params,
     * which will automatically convert array argument to Parameter instance.
     *
     * @param Database      $database Parent database.
     * @param QueryCompiler $compiler Driver specific QueryGrammar instance (one per builder).
     */
    public function __construct(Database $database, QueryCompiler $compiler)
    {
        $this->database = $database;
        $this->compiler = $compiler;
    }

    /**
     * Registering query parameters. Array parameters will be converted to Parameter object to correctly
     * resolve placeholders.
     *
     * @param mixed $parameter
     * @return mixed
     */
    protected function addParameter($parameter)
    {
        if (!$parameter instanceof ParameterInterface && is_array($parameter))
        {
            $parameter = new Parameter($parameter);
        }

        if ($parameter instanceof SqlFragmentInterface && !$parameter instanceof ParameterInterface)
        {
            return $parameter;
        }

        $this->parameters[] = $parameter;

        return $parameter;
    }

    /**
     * Helper methods used to correctly fetch and split identifiers provided by function parameters.
     * It support array list, string or comma separated list. Attention, this method will not work
     * with complex parameters (such as functions) provided as one comma separated string, please use
     * arrays in this case.
     *
     * @param array $identifiers
     * @return array
     */
    protected function fetchIdentifiers(array $identifiers)
    {
        if (count($identifiers) == 1 && is_string($identifiers[0]))
        {
            return array_map('trim', explode(',', $identifiers[0]));
        }

        if (count($identifiers) == 1 && is_array($identifiers[0]))
        {
            return $identifiers[0];
        }

        return $identifiers;
    }

    /**
     * Get query binder parameters. Method can be overloaded to perform some parameters manipulations.
     *
     * @return array
     */
    public function getParameters()
    {
        $parameters = [];
        foreach ($this->parameters as $parameter)
        {
            if ($parameter instanceof QueryBuilder)
            {
                $parameters = array_merge($parameters, $parameter->getParameters());
                continue;
            }

            $parameters[] = $parameter;
        }

        return $parameters;
    }

    /**
     * Get or render SQL statement.
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    abstract public function sqlStatement(QueryCompiler $compiler = null);

    /**
     * Run QueryBuilder statement against parent database. Method will be overloaded by child builder
     * to return correct value.
     *
     * @return \PDOStatement
     */
    public function run()
    {
        return $this->database->statement($this->sqlStatement(), $this->getParameters());
    }

    /**
     * Get interpolated (populated with parameters) SQL which will be run against database, please
     * use this method for debugging purposes only.
     *
     * @return string
     */
    public function queryString()
    {
        return DatabaseManager::interpolateQuery(
            $this->sqlStatement(),
            $this->database->getDriver()->prepareParameters($this->getParameters())
        );
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->sqlStatement();
    }

    /**
     * Simplified way to dump information.
     *
     * @return object
     */
    public function __debugInfo()
    {
        try
        {
            $queryString = $this->queryString();
        }
        catch (\Exception $exception)
        {
            $queryString = "[ERROR: {$exception->getMessage()}]";
        }

        $debugInfo = [
            'statement' => $queryString,
            'compiler'  => get_class($this->compiler),
            'database'  => $this->database
        ];

        return (object)$debugInfo;
    }
}