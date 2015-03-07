<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Tokenizer\Reflection\FunctionUsage;

use Spiral\Core\Component;

class Argument extends Component
{
    /**
     * Argument types.  All parsed functions will have arguments marked with these constants.
     *
     * Constant   - argument is scalar constant and not variable.
     * Variable   - PHP variable
     * Expression - PHP code (expression) which contains one or more variables.
     * String     - simple scalar string, value will be represent without "" or ''.
     */
    const CONSTANT   = 'constant';
    const VARIABLE   = 'variable';
    const EXPRESSION = 'expression';
    const STRING     = 'string';

    /**
     * Argument type.
     *
     * @var int
     */
    protected $type = null;

    /**
     * Argument value.
     *
     * @var string
     */
    protected $value = '';

    /**
     * Registering a new argument.
     *
     * @param mixed $type
     * @param mixed $value
     */
    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Argument is a PHP expression.
     *
     * @return bool
     */
    public function isSource()
    {
        return $this->type == self::EXPRESSION;
    }

    /**
     * Get the argument type.
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the argument value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Getting the argument string value (without braces).
     *
     * @return null|string
     */
    public function stringValue()
    {
        if ($this->type != self::STRING)
        {
            return null;
        }

        //The most reliable way
        return eval("return {$this->value};");
    }
}