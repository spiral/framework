<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Builders\Common;

use Spiral\Components\DBAL\DBALException;

trait HavingTrait
{
    /**
     * Array of having tokens declaring where conditions for HAVING statement. Structure and format
     * of this tokens are identical to whereTokens in WhereTrait.
     *
     * @see WhereTrait
     * @var array
     */
    protected $havingTokens = [];

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
    abstract protected function whereToken(
        $joiner,
        array $parameters,
        &$tokens = [],
        $catchParameters = true
    );

    /**
     * Add having condition to statement. Having condition will be specified with AND boolean joiner.
     * Method supports nested queries and array based (mongo like) conditions. Every provided parameter
     * will be automatically escaped in generated query. Syntax is identical to where methods.
     *
     * Examples:
     * 1) Simple token/nested query or expression
     * $select->having(new SQLFragment('(SELECT count(*) from `table`)'));
     *
     * 2) Simple assessment (= or IN)
     * $select->having('column', $value);
     * $select->having('column', array(1, 2, 3));
     * $select->having('column', new SQLFragment('CONCAT(columnA, columnB)'));
     *
     * 3) Assessment with specified operator (operator will be converted to uppercase automatically)
     * $select->having('column', '=', $value);
     * $select->having('column', 'IN', array(1, 2, 3));
     * $select->having('column', 'LIKE', $string);
     * $select->having('column', 'IN', new SQLFragment('(SELECT id from `table` limit 1)'));
     *
     * 4) Between and not between statements
     * $select->having('column', 'between', 1, 10);
     * $select->having('column', 'not between', 1, 10);
     * $select->having('column', 'not between', new SQLFragment('MIN(price)'), $maximum);
     *
     * 5) Closure with nested conditions
     * $this->having(function(WhereTrait $select){
     *      $select->having("name", "Wolfy-J")->orHaving("balance", ">", 100)
     * });
     *
     * 6) Array based condition
     * $select->having(["column" => 1]);
     * $select->having(["column" => [">" => 1, "<" => 10]]);
     * $select->having([
     *      "@or" => [
     *          ["id" => 1],
     *          ["column" => ["like" => "name"]]
     *      ]
     * ]);
     * $select->having([
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
     * @see WhereTrait
     * @see parseWhere()
     * @see whereToken()
     * @param string|mixed $identifier Column or expression.
     * @param mixed        $variousA   Operator or value.
     * @param mixed        $variousB   Value is operator specified.
     * @param mixed        $variousC   Specified only in between statements.
     * @return static
     * @throws DBALException
     */
    public function having($identifier, $variousA = null, $variousB = null, $variousC = null)
    {
        $this->whereToken('AND', func_get_args(), $this->havingTokens);

        return $this;
    }

    /**
     * Add having condition to statement. Having condition will be specified with AND boolean joiner.
     * Method supports nested queries and array based (mongo like) conditions. Every provided parameter
     * will be automatically escaped in generated query. Alias for having. Syntax is identical to where
     * methods.
     *
     * Examples:
     * 1) Simple token/nested query or expression
     * $select->andHaving(new SQLFragment('(SELECT count(*) from `table`)'));
     *
     * 2) Simple assessment (= or IN)
     * $select->andHaving('column', $value);
     * $select->andHaving('column', array(1, 2, 3));
     * $select->andHaving('column', new SQLFragment('CONCAT(columnA, columnB)'));
     *
     * 3) Assessment with specified operator (operator will be converted to uppercase automatically)
     * $select->andHaving('column', '=', $value);
     * $select->andHaving('column', 'IN', array(1, 2, 3));
     * $select->andHaving('column', 'LIKE', $string);
     * $select->andHaving('column', 'IN', new SQLFragment('(SELECT id from `table` limit 1)'));
     *
     * 4) Between and not between statements
     * $select->andHaving('column', 'between', 1, 10);
     * $select->andHaving('column', 'not between', 1, 10);
     * $select->andHaving('column', 'not between', new SQLFragment('MIN(price)'), $maximum);
     *
     * 5) Closure with nested conditions
     * $this->andHaving(function(WhereTrait $select){
     *      $select->having("name", "Wolfy-J")->orHaving("balance", ">", 100)
     * });
     *
     * 6) Array based condition
     * $select->andHaving(["column" => 1]);
     * $select->andHaving(["column" => [">" => 1, "<" => 10]]);
     * $select->andHaving([
     *      "id" => 1,
     *      "column" => ["like" => "name"]
     * ]);
     * $select->andHaving([
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
     * @see WhereTrait
     * @see parseWhere()
     * @see whereToken()
     * @param string|mixed $identifier Column or expression.
     * @param mixed        $variousA   Operator or value.
     * @param mixed        $variousB   Value is operator specified.
     * @param mixed        $variousC   Specified only in between statements.
     * @return static
     * @throws DBALException
     */
    public function andHaving($identifier, $variousA = null, $variousB = null, $variousC = null)
    {
        $this->whereToken('AND', func_get_args(), $this->havingTokens);

        return $this;
    }

    /**
     * Add having condition to statement. Having condition will be specified with OR boolean joiner.
     * Method supports nested queries and array based (mongo like) conditions. Every provided parameter
     * will be automatically escaped in generated query. Syntax is identical to where methods.
     *
     * Examples:
     * 1) Simple token/nested query or expression
     * $select->orHaving(new SQLFragment('(SELECT count(*) from `table`)'));
     *
     * 2) Simple assessment (= or IN)
     * $select->orHaving('column', $value);
     * $select->orHaving('column', array(1, 2, 3));
     * $select->orHaving('column', new SQLFragment('CONCAT(columnA, columnB)'));
     *
     * 3) Assessment with specified operator (operator will be converted to uppercase automatically)
     * $select->orHaving('column', '=', $value);
     * $select->orHaving('column', 'IN', array(1, 2, 3));
     * $select->orHaving('column', 'LIKE', $string);
     * $select->orHaving('column', 'IN', new SQLFragment('(SELECT id from `table` limit 1)'));
     *
     * 4) Between and not between statements
     * $select->orHaving('column', 'between', 1, 10);
     * $select->orHaving('column', 'not between', 1, 10);
     * $select->orHaving('column', 'not between', new SQLFragment('MIN(price)'), $maximum);
     *
     * 5) Closure with nested conditions
     * $this->orHaving(function(WhereTrait $select){
     *      $select->having("name", "Wolfy-J")->orHaving("balance", ">", 100)
     * });
     *
     * 6) Array based condition
     * $select->orHaving(["column" => 1]);
     * $select->orHaving(["column" => [">" => 1, "<" => 10]]);
     * $select->orHaving([
     *      "id" => 1,
     *      "column" => ["like" => "name"]
     * ]);
     * $select->orHaving([
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
     * @see WhereTrait
     * @see parseWhere()
     * @see whereToken()
     * @param string|mixed $identifier Column or expression.
     * @param mixed        $variousA   Operator or value.
     * @param mixed        $variousB   Value is operator specified.
     * @param mixed        $variousC   Specified only in between statements.
     * @return static
     * @throws DBALException
     */
    public function orHaving($identifier, $variousA = [], $variousB = null, $variousC = null)
    {
        $this->whereToken('OR', func_get_args(), $this->havingTokens);

        return $this;
    }
}