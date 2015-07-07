<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector;

use Spiral\Components\DBAL\Builders\Common\AbstractSelectQuery;

class WhereDecorator
{
    /**
     * Query instance to accept all where/onWhere/having requests.
     *
     * @var AbstractSelectQuery
     */
    protected $query = null;

    /**
     * Target function postfix. All requests will be routed using this pattern and "or", "and"
     * prefixes.
     *
     * @var string
     */
    protected $target = 'where';

    /**
     * Decorator will replace {table} with this alias in every where column.
     *
     * @var string
     */
    protected $alias = '';

    /**
     * WhereDecorator used to trick user functions and route where() calls to specified destination.
     *
     * @param AbstractSelectQuery $query
     * @param string              $target
     * @param string              $alias
     */
    public function __construct(AbstractSelectQuery $query, $target = 'where', $alias = '')
    {
        $this->query = $query;
        $this->target = $target;
        $this->alias = $alias;
    }

    /**
     * Get active routing target.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Update target method all where requests should be router into.
     *
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Helper function used to replace {@} alias with actual table name.
     *
     * @param mixed $where
     * @return mixed
     */
    protected function prepare($where)
    {
        if (is_string($where))
        {
            return str_replace('{@}', $this->alias, $where);
        }

        if (!is_array($where))
        {
            return $where;
        }

        $result = [];
        foreach ($where as $column => $value)
        {
            if (is_string($column) && !is_int($column))
            {
                $column = str_replace('{@}', $this->alias, $column);
            }

            $result[$column] = !is_array($value) ? $value : $this->prepare($value, $this->alias);
        }

        return $result;
    }

    /**
     * Add where condition to statement (routed to where, onWhere or having methods). Where condition
     * will be specified with AND boolean joiner. Method supports nested queries and array based (mongo
     * like) where conditions. Every provided parameter will be automatically escaped in generated query.
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
     */
    public function where($identifier, $variousA = null, $variousB = null, $variousC = null)
    {
        if ($identifier instanceof \Closure)
        {
            call_user_func($identifier, $this);

            return $this;
        }

        //We have to prepare only first argument
        $arguments = func_get_args();
        $arguments[0] = $this->prepare($arguments[0]);

        //Routing where
        call_user_func_array([$this->query, $this->target], $arguments);

        return $this;
    }

    /**
     * Add where condition to statement (routed to where, onWhere or having methods). Where condition
     * will be specified with AND boolean joiner. Method supports nested queries and array based (mongo
     * like) where conditions. Every provided parameter will be automatically escaped in generated query.
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
     */
    public function andWhere($identifier, $variousA = null, $variousB = null, $variousC = null)
    {
        if ($identifier instanceof \Closure)
        {
            call_user_func($identifier, $this);

            return $this;
        }

        //We have to prepare only first argument
        $arguments = func_get_args();
        $arguments[0] = $this->prepare($arguments[0]);

        //Routing where
        call_user_func_array([$this->query, 'and' . ucfirst($this->target)], $arguments);

        return $this;
    }

    /**
     * Add where condition to statement (routed to where, onWhere or having methods). Where condition
     * will be specified with OR boolean joiner. Method supports nested queries and array based (mongo
     * like) where conditions. Every provided parameter will be automatically escaped in generated query.
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
     */
    public function orWhere($identifier, $variousA = [], $variousB = null, $variousC = null)
    {
        if ($identifier instanceof \Closure)
        {
            call_user_func($identifier, $this);

            return $this;
        }

        //We have to prepare only first argument
        $arguments = func_get_args();
        $arguments[0] = $this->prepare($arguments[0]);

        //Routing where
        call_user_func_array([$this->query, 'or' . ucfirst($this->target)], $arguments);

        return $this;
    }
}