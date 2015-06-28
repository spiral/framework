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

trait JoinTrait
{
    /**
     * Array of joined tables with specified JOIN type (LEFT, RIGHT, INNER) and ON conditions.
     * Joined table can define alias which will be handled in columns and were conditions.
     *
     * @var array
     */
    protected $joins = [];

    /**
     * Name of last join, next on() or orOn() method calls will attached conditions to that join.
     *
     * @var string
     */
    protected $currentJoin = null;

    /**
     * Register new INNER table join, all future on() method calls will associate conditions to this
     * join.
     *
     * Examples:
     * $select->join('info', 'userID', 'users.id')->columns('info.balance');
     * $select->join('info', 'userID', '=', 'users.id')->columns('info.balance');
     * $select->join('info', ['userID' => 'users.id'])->columns('info.balance');
     *
     * $select->join('info', function($select) {
     *      $select->on('userID', 'users.id')->orOn('userID', 'users.masterID');
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->join('info as i', 'i.userID', 'users.id')->columns('i.balance');
     * $select->join('info as i', 'i.userID', '=', 'users.id')->columns('i.balance');
     * $select->join('info as i', ['i.userID' => 'users.id'])->columns('i.balance');
     *
     * $select->join('info as i', function($select) {
     *      $select->on('i.userID', 'users.id')->orOn('i.userID', 'users.masterID');
     * })->columns('i.balance');
     *
     * Join aliases can be used in columns, where conditions, having conditions, order by, sort by
     * and aggregations.
     *
     * @link http://www.w3schools.com/sql/sql_join_inner.asp
     * @param string $table Joined table name (without prefix), can have defined alias.
     * @param mixed  $on    Where parameters, closure of array of where conditions.
     * @return static
     */
    public function join($table, $on = null)
    {
        $this->joins[$this->currentJoin = $table] = ['type' => 'INNER', 'on' => []];

        return call_user_func_array([$this, 'on'], array_slice(func_get_args(), 1));
    }

    /**
     * Register new INNER table join, all future on() method calls will associate conditions to this
     * join.
     *
     * Examples:
     * $select->innerJoin('info', 'userID', 'users.id')->columns('info.balance');
     * $select->innerJoin('info', 'userID', '=', 'users.id')->columns('info.balance');
     * $select->innerJoin('info', ['userID' => 'users.id'])->columns('info.balance');
     *
     * $select->innerJoin('info', function($select) {
     *      $select->on('userID', 'users.id')->orOn('userID', 'users.masterID');
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->innerJoin('info as i', 'i.userID', 'users.id')->columns('i.balance');
     * $select->innerJoin('info as i', 'i.userID', '=', 'users.id')->columns('i.balance');
     * $select->innerJoin('info as i', ['i.userID' => 'users.id'])->columns('i.balance');
     *
     * $select->innerJoin('info as i', function($select) {
     *      $select->on('i.userID', 'users.id')->orOn('i.userID', 'users.masterID');
     * })->columns('i.balance');
     *
     * Join aliases can be used in columns, where conditions, having conditions, order by, sort by
     * and aggregations.
     *
     * @link http://www.w3schools.com/sql/sql_join_inner.asp
     * @param string $table Joined table name (without prefix), can have defined alias.
     * @param mixed  $on    Where parameters, closure of array of where conditions.
     * @return static
     */
    public function innerJoin($table, $on = null)
    {
        $this->joins[$this->currentJoin = $table] = ['type' => 'RIGHT', 'on' => []];

        return call_user_func_array([$this, 'on'], array_slice(func_get_args(), 1));
    }

    /**
     * Register new RIGHT table join, all future on() method calls will associate conditions to this
     * join.
     *
     * Examples:
     * $select->rightJoin('info', 'userID', 'users.id')->columns('info.balance');
     * $select->rightJoin('info', 'userID', '=', 'users.id')->columns('info.balance');
     * $select->rightJoin('info', ['userID' => 'users.id'])->columns('info.balance');
     *
     * $select->rightJoin('info', function($select) {
     *      $select->on('userID', 'users.id')->orOn('userID', 'users.masterID');
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->rightJoin('info as i', 'i.userID', 'users.id')->columns('i.balance');
     * $select->rightJoin('info as i', 'i.userID', '=', 'users.id')->columns('i.balance');
     * $select->rightJoin('info as i', ['i.userID' => 'users.id'])->columns('i.balance');
     *
     * $select->rightJoin('info as i', function($select) {
     *      $select->on('i.userID', 'users.id')->orOn('i.userID', 'users.masterID');
     * })->columns('i.balance');
     *
     * Join aliases can be used in columns, where conditions, having conditions, order by, sort by
     * and aggregations.
     *
     * @link http://www.w3schools.com/sql/sql_join_right.asp
     * @param string $table Joined table name (without prefix), can have defined alias.
     * @param mixed  $on    Where parameters, closure of array of where conditions.
     * @return static
     */
    public function rightJoin($table, $on = null)
    {
        $this->joins[$this->currentJoin = $table] = ['type' => 'RIGHT', 'on' => []];

        return call_user_func_array([$this, 'on'], array_slice(func_get_args(), 1));
    }

    /**
     * Register new LEFT table join, all future on() method calls will associate conditions to this
     * join.
     *
     * Examples:
     * $select->leftJoin('info', 'userID', 'users.id')->columns('info.balance');
     * $select->leftJoin('info', 'userID', '=', 'users.id')->columns('info.balance');
     * $select->leftJoin('info', ['userID' => 'users.id'])->columns('info.balance');
     *
     * $select->leftJoin('info', function($select) {
     *      $select->on('userID', 'users.id')->orOn('userID', 'users.masterID');
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->leftJoin('info as i', 'i.userID', 'users.id')->columns('i.balance');
     * $select->leftJoin('info as i', 'i.userID', '=', 'users.id')->columns('i.balance');
     * $select->leftJoin('info as i', ['i.userID' => 'users.id'])->columns('i.balance');
     *
     * $select->leftJoin('info as i', function($select) {
     *      $select->on('i.userID', 'users.id')->orOn('i.userID', 'users.masterID');
     * })->columns('i.balance');
     *
     * Join aliases can be used in columns, where conditions, having conditions, order by, sort by
     * and aggregations.
     *
     * @link http://www.w3schools.com/sql/sql_join_left.asp
     * @param string $table Joined table name (without prefix), can have defined alias.
     * @param mixed  $on    Where parameters, closure of array of where conditions.
     * @return static
     */
    public function leftJoin($table, $on = null)
    {
        $this->joins[$this->currentJoin = $table] = ['type' => 'LEFT', 'on' => []];

        return call_user_func_array([$this, 'on'], array_slice(func_get_args(), 1));
    }

    /**
     * Register new FULL table join, all future on() method calls will associate conditions to this
     * join.
     *
     * Examples:
     * $select->fullJoin('info', 'userID', 'users.id')->columns('info.balance');
     * $select->fullJoin('info', 'userID', '=', 'users.id')->columns('info.balance');
     * $select->fullJoin('info', ['userID' => 'users.id'])->columns('info.balance');
     *
     * $select->fullJoin('info', function($select) {
     *      $select->on('userID', 'users.id')->orOn('userID', 'users.masterID');
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->fullJoin('info as i', 'i.userID', 'users.id')->columns('i.balance');
     * $select->fullJoin('info as i', 'i.userID', '=', 'users.id')->columns('i.balance');
     * $select->fullJoin('info as i', ['i.userID' => 'users.id'])->columns('i.balance');
     *
     * $select->fullJoin('info as i', function($select) {
     *      $select->on('i.userID', 'users.id')->orOn('i.userID', 'users.masterID');
     * })->columns('i.balance');
     *
     * Join aliases can be used in columns, where conditions, having conditions, order by, sort by
     * and aggregations.
     *
     * @link http://www.w3schools.com/sql/sql_join_left.asp
     * @param string $table Joined table name (without prefix), can have defined alias.
     * @param mixed  $on    Where parameters, closure of array of where conditions.
     * @return static
     */
    public function fullJoin($table, $on = null)
    {
        $this->joins[$this->currentJoin = $table] = ['type' => 'LEFT', 'on' => []];

        return call_user_func_array([$this, 'on'], array_slice(func_get_args(), 1));
    }

    /**
     * Add on condition to last registered join. On condition will be specified with AND boolean
     * joiner. Method supports nested queries and array based (mongo like) where conditions. Syntax
     * is identical to where methods except no arguments should be identifiers and not values.
     *
     * Examples:
     * $select->join('info')->on('userID', 'users.id')->columns('info.balance');
     * $select->join('info')->on('userID', '=', 'users.id')->columns('info.balance');
     * $select->join('info')->on(['userID' => 'users.id'])->columns('info.balance');
     *
     * $select->join('info')->on(function($select) {
     *      $select->on('userID', 'users.id')->orOn('userID', 'users.masterID');
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->join('info as i')->on('i.userID', 'users.id')->columns('i.balance');
     * $select->join('info as i')->on('i.userID', '=', 'users.id')->columns('i.balance');
     * $select->join('info as i')->on(['i.userID' => 'users.id'])->columns('i.balance');
     *
     * $select->join('info as i')->on(function($select) {
     *      $select->on('i.userID', 'users.id')->orOn('i.userID', 'users.masterID');
     * })->columns('i.balance');
     *
     * @see parseWhere()
     * @see whereToken()
     * @param mixed $on         Joined column name or SQLFragment, or where array.
     * @param mixed $operator   Foreign column is operator specified.
     * @param mixed $identifier Foreign column.
     * @return static
     * @throws DBALException
     */
    public function on($on = null, $operator = null, $identifier = null)
    {
        $this->whereToken('AND', func_get_args(), $this->joins[$this->currentJoin]['on'], false);

        return $this;
    }

    /**
     * Add on condition to last registered join. On condition will be specified with AND boolean
     * joiner. Method supports nested queries and array based (mongo like) where conditions. Syntax
     * is identical to where methods except no arguments should be identifiers and not values. Alias
     * for on() method.
     *
     * Examples:
     * $select->join('info')->andOn('userID', 'users.id')->columns('info.balance');
     * $select->join('info')->andOn('userID', '=', 'users.id')->columns('info.balance');
     * $select->join('info')->andOn(['userID' => 'users.id'])->columns('info.balance');
     *
     * $select->join('info')->andOn(function($select) {
     *      $select->on('userID', 'users.id')->orOn('userID', 'users.masterID');
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->join('info as i')->andOn('i.userID', 'users.id')->columns('i.balance');
     * $select->join('info as i')->andOn('i.userID', '=', 'users.id')->columns('i.balance');
     * $select->join('info as i')->andOn(['i.userID' => 'users.id'])->columns('i.balance');
     *
     * $select->join('info as i')->andOn(function($select) {
     *      $select->on('i.userID', 'users.id')->orOn('i.userID', 'users.masterID');
     * })->columns('i.balance');
     *
     * @see parseWhere()
     * @see whereToken()
     * @param mixed $on         Joined column name or SQLFragment, or where array.
     * @param mixed $operator   Foreign column is operator specified.
     * @param mixed $identifier Foreign column.
     * @return static
     * @throws DBALException
     */
    public function andOn($on = null, $operator = null, $identifier = null)
    {
        $this->whereToken('AND', func_get_args(), $this->joins[$this->currentJoin]['on'], false);

        return $this;
    }

    /**
     * Add on condition to last registered join. On condition will be specified with OR boolean
     * joiner. Method supports nested queries and array based (mongo like) where conditions. Syntax
     * is identical to where methods except no arguments should be identifiers and not values.
     *
     * Examples:
     * $select->join('info')
     *         ->on('i.userID', 'users.masterID')
     *          ->orOn('userID', 'users.id')
     *          ->columns('info.balance');
     *
     * $select->join('info')
     *         ->on('i.userID', 'users.masterID')
     *         ->orOn('userID', '=', 'users.id')
     *         ->columns('info.balance');
     *
     * $select->join('info')
     *         ->on('i.userID', 'users.masterID')
     *         ->orOn(['userID' => 'users.id'])
     *         ->columns('info.balance');
     *
     * $select->join('info')->on('i.userID', 'users.masterID')->orOn(function($select) {
     *      $select->on('userID', 'users.id')->orOn('userID', 'users.masterID');
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->join('info as i')
     *         ->on('i.userID', 'users.masterID')
     *         ->orOn('i.userID', 'users.id')
     *         ->columns('i.balance');
     *
     * $select->join('info as i')
     *         ->on('i.userID', 'users.masterID')
     *         ->orOn('i.userID', '=', 'users.id')
     *         ->columns('i.balance');
     *
     * $select->join('info as i')
     *         ->on('i.userID', 'users.masterID')
     *         ->orOn(['i.userID' => 'users.id'])
     *         ->columns('i.balance');
     *
     * $select->join('info as i')->on('i.userID', 'users.masterID')->orOn(function($select) {
     *      $select->on('i.userID', 'users.id')->orOn('i.userID', 'users.masterID');
     * })->columns('i.balance');
     *
     * @see parseWhere()
     * @see whereToken()
     * @param mixed $on         Joined column name or SQLFragment, or where array.
     * @param mixed $operator   Foreign column is operator specified.
     * @param mixed $identifier Foreign column.
     * @return static
     * @throws DBALException
     */
    public function orOn($on = null, $operator = null, $identifier = null)
    {
        $this->whereToken('AND', func_get_args(), $this->joins[$this->currentJoin]['on'], false);

        return $this;
    }

    /**
     * Add on condition to last registered join. On condition will be specified with AND boolean
     * joiner. Method supports nested queries and array based (mongo like) where conditions. Syntax
     * is identical to where methods except no arguments should be identifiers and not values.
     *
     * Attention: THIS METHOD WILL ADD PARAMETRIC CONDITION TO ON, USE "on" TO ADD column relations.
     *
     * Examples:
     * $select->join('info')->onWhere('someValue', $value)->columns('info.balance');
     * $select->join('info')->onWhere('someValue', '=', $value)->columns('info.balance');
     * $select->join('info')->onWhere(['someValue' => $value])->columns('info.balance');
     *
     * $select->join('info')->onWhere(function($select) use($value, $anotherValue) {
     *      $select->onWhere('someValue', $value)->orOnWhere('someValue', $anotherValue);
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->join('info as i')->onWhere('i.someValue', $value)->columns('i.balance');
     * $select->join('info as i')->onWhere('i.someValue', '=', $value)->columns('i.balance');
     * $select->join('info as i')->onWhere(['i.someValue' => $value])->columns('i.balance');
     *
     * $select->join('info as i')->onWhere(function($select) use($value, $anotherValue) {
     *      $select->onWhere('i.someValue', $value)->orOnWhere('i.someValue', $anotherValue);
     * })->columns('i.balance');
     *
     * @see parseWhere()
     * @see whereToken()
     * @param mixed $on       Joined column name or SQLFragment, or where array.
     * @param mixed $operator Foreign column is operator specified.
     * @param mixed $value    Value.
     * @return static
     * @throws DBALException
     */
    public function onWhere($on = null, $operator = null, $value = null)
    {
        $this->whereToken('AND', func_get_args(), $this->joins[$this->currentJoin]['on'], true);

        return $this;
    }

    /**
     * Add on condition to last registered join. On condition will be specified with AND boolean
     * joiner. Method supports nested queries and array based (mongo like) where conditions. Syntax
     * is identical to where methods except no arguments should be identifiers and not values.
     *
     * Attention: THIS METHOD WILL ADD PARAMETRIC CONDITION TO ON, USE "on" TO ADD column relations.
     *
     * Examples:
     * $select->join('info')->andOnWhere('someValue', $value)->columns('info.balance');
     * $select->join('info')->andOnWhere('someValue', '=', $value)->columns('info.balance');
     * $select->join('info')->andOnWhere(['someValue' => $value])->columns('info.balance');
     *
     * $select->join('info')->andOnWhere(function($select) use($value, $anotherValue) {
     *      $select->onWhere('someValue', $value)->orOnWhere('someValue', $anotherValue);
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->join('info as i')->andOnWhere('i.someValue', $value)->columns('i.balance');
     * $select->join('info as i')->andOnWhere('i.someValue', '=', $value)->columns('i.balance');
     * $select->join('info as i')->andOnWhere(['i.someValue' => $value])->columns('i.balance');
     *
     * $select->join('info as i')->andOnWhere(function($select) use($value, $anotherValue) {
     *      $select->onWhere('i.someValue', $value)->orOnWhere('i.someValue', $anotherValue);
     * })->columns('i.balance');
     *
     * @see parseWhere()
     * @see whereToken()
     * @param mixed $on       Joined column name or SQLFragment, or where array.
     * @param mixed $operator Foreign column is operator specified.
     * @param mixed $value    Value.
     * @return static
     * @throws DBALException
     */
    public function andOnWhere($on = null, $operator = null, $value = null)
    {
        $this->whereToken('AND', func_get_args(), $this->joins[$this->currentJoin]['on'], true);

        return $this;
    }

    /**
     * Add on condition to last registered join. On condition will be specified with OR boolean
     * joiner. Method supports nested queries and array based (mongo like) where conditions. Syntax
     * is identical to where methods except no arguments should be identifiers and not values.
     *
     * Attention: THIS METHOD WILL ADD PARAMETRIC CONDITION TO ON, USE "on" TO ADD column relations.
     *
     * Examples:
     * $select->join('info')->orOnWhere('someValue', $value)->columns('info.balance');
     * $select->join('info')->orOnWhere('someValue', '=', $value)->columns('info.balance');
     * $select->join('info')->orOnWhere(['someValue' => $value])->columns('info.balance');
     *
     * $select->join('info')->orOnWhere(function($select) use($value, $anotherValue) {
     *      $select->onWhere('someValue', $value)->orOnWhere('someValue', $anotherValue);
     * })->columns('info.balance');
     *
     * Aliases can be also used:
     * $select->join('info as i')->orOnWhere('i.someValue', $value)->columns('i.balance');
     * $select->join('info as i')->orOnWhere('i.someValue', '=', $value)->columns('i.balance');
     * $select->join('info as i')->orOnWhere(['i.someValue' => $value])->columns('i.balance');
     *
     * $select->join('info as i')->orOnWhere(function($select) use($value, $anotherValue) {
     *      $select->onWhere('i.someValue', $value)->orOnWhere('i.someValue', $anotherValue);
     * })->columns('i.balance');
     *
     * @see parseWhere()
     * @see whereToken()
     * @param mixed $on       Joined column name or SQLFragment, or where array.
     * @param mixed $operator Foreign column is operator specified.
     * @param mixed $value    Value.
     * @return static
     * @throws DBALException
     */
    public function orOnWhere($on = null, $operator = null, $value = null)
    {
        $this->whereToken('AND', func_get_args(), $this->joins[$this->currentJoin]['on'], true);

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
     * @param bool   $dataParameters  If true every found parameter will passed thought addParameter()
     *                                method, if false every parameter will be converted to identifier.
     * @return array
     * @throws DBALException
     */
    abstract protected function whereToken(
        $joiner,
        array $parameters,
        &$tokens = [],
        $dataParameters = true
    );
}