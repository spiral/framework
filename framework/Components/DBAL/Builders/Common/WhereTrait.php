<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Builders\Common;

use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\DBALException;
use Spiral\Components\DBAL\QueryBuilder;

trait WhereTrait
{
    /**
     * WhereTrait organize where construction using token structure which includes token joiner (OR,
     * AND) and token context, this set of tokens can be used to represent almost any query string
     * and can be compiled by QueryGrammar->compileWhere() method. Even if token context will contain
     * original value, this value will be replaced with placeholder in generated query.
     *
     * @var array
     */
    protected $whereTokens = array();

    /**
     * Registering query parameters. Array parameters will be converted to Parameter object to
     * correctly resolve placeholders.
     *
     * @param mixed $parameter
     * @return mixed
     */
    abstract protected function addParameter($parameter);

    /**
     * Add where condition to statement. Where condition will be specified with AND boolean joiner.
     * Method supports nested queries and array based (mongo like) where conditions. Every provided
     * parameter will be automatically escaped in generated query.
     *
     * Examples:
     * 1) Simple token/nested query or expression
     * $select->where(new SQLFragment('(SELECT count(*) from `table`)'));
     *
     * 2) Simple assessment (= or IN)
     * $select->where('column', $value);
     * $select->where('column', array(1, 2, 3));
     * $select->where('column', new SQLFragment('CONCAT(columnA, columnB)'));
     *
     * 3) Assessment with specified operator (operator will be converted to uppercase automatically)
     * $select->where('column', '=', $value);
     * $select->where('column', 'IN', array(1, 2, 3));
     * $select->where('column', 'LIKE', $string);
     * $select->where('column', 'IN', new SQLFragment('(SELECT id from `table` limit 1)'));
     *
     * 4) Between and not between statements
     * $select->where('column', 'between', 1, 10);
     * $select->where('column', 'not between', 1, 10);
     * $select->where('column', 'not between', new SQLFragment('MIN(price)'), $maximum);
     *
     * 5) Closure with nested conditions
     * $this->where(function(WhereTrait $select){
     *      $select->where("name", "Wolfy-J")->orWhere("balance", ">", 100)
     * });
     *
     * 6) Array based condition
     * $select->where(["column" => 1]);
     * $select->where(["column" => [">" => 1, "<" => 10]]);
     * $select->where([
     *      "@or" => [
     *          ["id" => 1],
     *          ["column" => ["like" => "name"]]
     *      ]
     * ]);
     * $select->where([
     *      '@or' => [
     *          ["id" => 1],
     *          ["id" => 2],
     *          ["id" => 3],
     *          ["id" => 4],
     *          ["id" => 5],
     *      ],
     *      "column" => [
     *          "like" => "name"
     *      ],
     *      'x'      => [
     *          '>' => 1,
     *          '<' => 10
     *      ]
     * ]);
     *
     * You can read more about complex where statements in official documentation or look mongo
     * queries examples.
     *
     * @see parseWhere()
     * @see whereToken()
     * @param string|mixed $identifier Column or expression.
     * @param mixed        $variousA   Operator or value.
     * @param mixed        $variousB   Value is operator specified.
     * @param mixed        $variousC   Specified only in between statements.
     * @return static
     * @throws DBALException
     */
    public function where($identifier, $variousA = null, $variousB = null, $variousC = null)
    {
        $this->whereToken('AND', func_get_args(), $this->whereTokens);

        return $this;
    }

    /**
     * Add where condition to statement. Where condition will be specified with AND boolean joiner.
     * Method supports nested queries and array based (mongo like) where conditions. Every provided
     * parameter will be automatically escaped in generated query. Alias for where.
     *
     * Examples:
     * 1) Simple token/nested query or expression
     * $select->andWhere(new SQLFragment('(SELECT count(*) from `table`)'));
     *
     * 2) Simple assessment (= or IN)
     * $select->andWhere('column', $value);
     * $select->andWhere('column', array(1, 2, 3));
     * $select->andWhere('column', new SQLFragment('CONCAT(columnA, columnB)'));
     *
     * 3) Assessment with specified operator (operator will be converted to uppercase automatically)
     * $select->andWhere('column', '=', $value);
     * $select->andWhere('column', 'IN', array(1, 2, 3));
     * $select->andWhere('column', 'LIKE', $string);
     * $select->andWhere('column', 'IN', new SQLFragment('(SELECT id from `table` limit 1)'));
     *
     * 4) Between and not between statements
     * $select->andWhere('column', 'between', 1, 10);
     * $select->andWhere('column', 'not between', 1, 10);
     * $select->andWhere('column', 'not between', new SQLFragment('MIN(price)'), $maximum);
     *
     * 5) Closure with nested conditions
     * $this->andWhere(function(WhereTrait $select){
     *      $select->where("name", "Wolfy-J")->orWhere("balance", ">", 100)
     * });
     *
     * 6) Array based condition
     * $select->andWhere(["column" => 1]);
     * $select->andWhere(["column" => [">" => 1, "<" => 10]]);
     * $select->andWhere([
     *      "id" => 1,
     *      "column" => ["like" => "name"]
     * ]);
     * $select->andWhere([
     *      '@or' => [
     *          ["id" => 1],
     *          ["id" => 2],
     *          ["id" => 3],
     *          ["id" => 4],
     *          ["id" => 5],
     *      ],
     *      "column" => [
     *          "like" => "name"
     *      ],
     *      'x'      => [
     *          '>' => 1,
     *          '<' => 10
     *      ]
     * ]);
     *
     * You can read more about complex where statements in official documentation or look mongo
     * queries examples.
     *
     * @see parseWhere()
     * @see whereToken()
     * @param string|mixed $identifier Column or expression.
     * @param mixed        $variousA   Operator or value.
     * @param mixed        $variousB   Value is operator specified.
     * @param mixed        $variousC   Specified only in between statements.
     * @return static
     * @throws DBALException
     */
    public function andWhere($identifier, $variousA = null, $variousB = null, $variousC = null)
    {
        $this->whereToken('AND', func_get_args(), $this->whereTokens);

        return $this;
    }

    /**
     * Add where condition to statement. Where condition will be specified with OR boolean joiner.
     * Method supports nested queries and array based (mongo like) where conditions. Every provided
     * parameter will be automatically escaped in generated query.
     *
     * Examples:
     * 1) Simple token/nested query or expression
     * $select->orWhere(new SQLFragment('(SELECT count(*) from `table`)'));
     *
     * 2) Simple assessment (= or IN)
     * $select->orWhere('column', $value);
     * $select->orWhere('column', array(1, 2, 3));
     * $select->orWhere('column', new SQLFragment('CONCAT(columnA, columnB)'));
     *
     * 3) Assessment with specified operator (operator will be converted to uppercase automatically)
     * $select->orWhere('column', '=', $value);
     * $select->orWhere('column', 'IN', array(1, 2, 3));
     * $select->orWhere('column', 'LIKE', $string);
     * $select->orWhere('column', 'IN', new SQLFragment('(SELECT id from `table` limit 1)'));
     *
     * 4) Between and not between statements
     * $select->orWhere('column', 'between', 1, 10);
     * $select->orWhere('column', 'not between', 1, 10);
     * $select->orWhere('column', 'not between', new SQLFragment('MIN(price)'), $maximum);
     *
     * 5) Closure with nested conditions
     * $this->orWhere(function(WhereTrait $select){
     *      $select->where("name", "Wolfy-J")->orWhere("balance", ">", 100)
     * });
     *
     * 6) Array based condition
     * $select->orWhere(["column" => 1]);
     * $select->orWhere(["column" => [">" => 1, "<" => 10]]);
     * $select->orWhere([
     *      "id" => 1,
     *      "column" => ["like" => "name"]
     * ]);
     * $select->orWhere([
     *      '@or' => [
     *          ["id" => 1],
     *          ["id" => 2],
     *          ["id" => 3],
     *          ["id" => 4],
     *          ["id" => 5],
     *      ],
     *      "column" => [
     *          "like" => "name"
     *      ],
     *      'x'      => [
     *          '>' => 1,
     *          '<' => 10
     *      ]
     * ]);
     *
     * You can read more about complex where statements in official documentation or look mongo
     * queries examples.
     *
     * @see parseWhere()
     * @see whereToken()
     * @param string|mixed $identifier Column or expression.
     * @param mixed        $variousA   Operator or value.
     * @param mixed        $variousB   Value is operator specified.
     * @param mixed        $variousC   Specified only in between statements.
     * @return static
     * @throws DBALException
     */
    public function orWhere($identifier, $variousA = array(), $variousB = null, $variousC = null)
    {
        $this->whereToken('OR', func_get_args(), $this->whereTokens);

        return $this;
    }

    /**
     * Helper methods used to processed user input in where methods to internal where token, method
     * support all different combinations, closures and nested queries. Additionally i can be used
     * not only for where but for having and join tokens.
     *
     * @param string $joiner          Boolean joiner (AND|OR).
     * @param array  $parameters      Set of parameters collected from where functions.
     * @param array  $tokens          Array to aggregate compiled tokens.
     * @param bool   $catchParameters If true every found parameter will passed thought addParameter()
     *                                method.
     * @return array
     * @throws DBALException
     */
    protected function whereToken(
        $joiner,
        array $parameters,
        &$tokens = array(),
        $catchParameters = true
    )
    {
        list($identifier, $variousA, $variousB, $variousC) = $parameters + array_fill(0, 5, null);

        //Complex query is provided
        if (is_array($identifier))
        {
            return $this->parseWhere(
                $identifier,
                $joiner == 'AND' ? DatabaseManager::TOKEN_AND : DatabaseManager::TOKEN_OR,
                $tokens,
                $catchParameters
            );
        }

        if ($identifier instanceof \Closure)
        {
            $tokens[] = array($joiner, '(');
            call_user_func($identifier, $this, $joiner, $catchParameters);
            $tokens[] = array('', ')');

            return $tokens;
        }

        if ($identifier instanceof QueryBuilder && $catchParameters)
        {
            //This will copy all parameters from QueryBuilder
            $this->addParameter($identifier);
        }

        switch (count($parameters))
        {
            case 1:
                //A single token
                $tokens[] = array($joiner, $identifier);
                break;
            case 2:
                //Simple condition
                $tokens[] = array($joiner, array(
                    $identifier,
                    '=',
                    $catchParameters ? $this->addParameter($variousA) : $variousA
                ));
                break;
            case 3:
                //Operator is specified
                $tokens[] = array($joiner, array(
                    $identifier,
                    strtoupper($variousA),
                    $catchParameters ? $this->addParameter($variousB) : $variousB
                ));
                break;
            case 4:
                //BETWEEN or NOT BETWEEN
                $variousA = strtoupper($variousA);
                if (!in_array($variousA, array('BETWEEN', 'NOT BETWEEN')))
                {
                    throw new DBALException(
                        'Only "BETWEEN" or "NOT BETWEEN" can define second comparasions value.'
                    );
                }

                $tokens[] = array($joiner, array(
                    $identifier,
                    strtoupper($variousA),
                    $catchParameters ? $this->addParameter($variousB) : $variousB,
                    $catchParameters ? $this->addParameter($variousC) : $variousC
                ));
        }

        return $tokens;
    }

    /**
     * Helper method used to convert complex where statement (specified by array, mongo like) to set
     * of where tokens. Method support simple expressions, nested, or and and groups and etc.
     *
     * Examples:
     * $select->where(["column" => 1]);
     *
     * $select->where(["column" => [">" => 1, "<" => 10]]);
     *
     * $select->where([
     *      "@or" => [
     *          ["id" => 1],
     *          ["column" => ["like" => "name"]]
     *      ]
     * ]);
     *
     * $select->where([
     *      '@or' => [
     *          ["id" => 1],
     *          ["id" => 2],
     *          ["id" => 3],
     *          ["id" => 4],
     *          ["id" => 5],
     *      ],
     *      "column" => [
     *          "like" => "name"
     *      ],
     *      'x'      => [
     *          '>' => 1,
     *          '<' => 10
     *      ]
     * ]);
     *
     * @param array  $where           Array of where conditions.
     * @param string $grouping        Parent grouping token (OR, AND)
     * @param array  $tokens          Array to aggregate compiled tokens.
     * @param bool   $catchParameters If true every found parameter will passed thought addParameter()
     *                                method.
     * @return array
     * @throws DBALException
     */
    protected function parseWhere(array $where, $grouping, &$tokens, $catchParameters = true)
    {
        foreach ($where as $name => $value)
        {
            //Grouping identifier (@OR, @AND), Mongo like style
            if (
                strtoupper($name) == DatabaseManager::TOKEN_AND
                || strtoupper($name) == DatabaseManager::TOKEN_OR
            )
            {
                $tokens[] = array($grouping == DatabaseManager::TOKEN_AND ? 'AND' : 'OR', '(');

                foreach ($value as $subWhere)
                {
                    $this->parseWhere($subWhere, strtoupper($name), $tokens, $catchParameters);
                }

                $tokens[] = array('', ')');
                continue;
            }

            if (!is_array($value))
            {
                //Simple association
                $tokens[] = array(
                    $grouping == DatabaseManager::TOKEN_AND ? 'AND' : 'OR',
                    array($name, '=', $catchParameters ? $this->addParameter($value) : $value)
                );
                continue;
            }

            $innerJoiner = $grouping == DatabaseManager::TOKEN_AND ? 'AND' : 'OR';
            if (count($value) > 1)
            {
                $tokens[] = array($grouping == DatabaseManager::TOKEN_AND ? 'AND' : 'OR', '(');
                $innerJoiner = 'AND';
            }

            foreach ($value as $key => $subValue)
            {
                if (is_numeric($key))
                {
                    throw new DBALException("Nested conditions should have defined operator.");
                }
                $key = strtoupper($key);
                if (in_array($key, array('BETWEEN', 'NOT BETWEEN')))
                {
                    if (!is_array($subValue) || count($subValue) != 2)
                    {
                        throw new DBALException(
                            "Exactly 2 array values required for between statement."
                        );
                    }

                    //One complex operation
                    $tokens[] = array($innerJoiner, array(
                        $name,
                        $key,
                        $catchParameters ? $this->addParameter($subValue) : $subValue[0],
                        $catchParameters ? $this->addParameter($subValue[1]) : $subValue[1]
                    ));
                }
                else
                {
                    //One complex operation
                    $tokens[] = array($innerJoiner, array(
                        $name,
                        $key,
                        $catchParameters ? $this->addParameter($subValue) : $subValue
                    ));
                }
            }

            if (count($value) > 1)
            {
                $tokens[] = array('', ')');
            }
        }

        return $tokens;
    }
}