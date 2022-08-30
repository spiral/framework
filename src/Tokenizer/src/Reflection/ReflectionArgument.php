<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tokenizer\Reflection;

use Spiral\Tokenizer\Exception\ReflectionException;

/**
 * Represent argument using in method or function invocation with it's type and value.
 */
final class ReflectionArgument
{
    /**
     * Argument types.
     */
    public const CONSTANT   = 'constant';   //Scalar constant and not variable.
    public const VARIABLE   = 'variable';   //PHP variable
    public const EXPRESSION = 'expression'; //PHP code (expression).
    public const STRING     = 'string';     //Simple scalar string, can be fetched using stringValue().

    /** @var string */
    private $type;

    /** @var string */
    private $value;

    /**
     * New instance of ReflectionArgument.
     *
     * @param string $type  Argument type (see top constants).
     * @param string $value Value in a form of php code.
     */
    public function __construct($type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Convert argument value into valid string. Can be applied only for STRING type arguments.
     *
     *
     * @throws ReflectionException When value can not be converted into string.
     */
    public function stringValue(): string
    {
        if ($this->type != self::STRING) {
            throw new ReflectionException(
                "Unable to represent value as string, value type is '{$this->type}'"
            );
        }

        //The most reliable way
        return eval("return {$this->value};");
    }

    /**
     * Create Argument reflections based on provided set of tokens (fetched from invoke).
     *
     * @return self[]
     */
    public static function locateArguments(array $tokens): array
    {
        $definition = null;
        $level = 0;

        $result = [];
        foreach ($tokens as $token) {
            if ($token[ReflectionFile::TOKEN_TYPE] == T_WHITESPACE) {
                continue;
            }

            if (empty($definition)) {
                $definition = ['type' => self::EXPRESSION, 'value' => '', 'tokens' => []];
            }

            if (
                $token[ReflectionFile::TOKEN_TYPE] == '('
                || $token[ReflectionFile::TOKEN_TYPE] == '['
            ) {
                ++$level;
                $definition['value'] .= $token[ReflectionFile::TOKEN_CODE];
                continue;
            }

            if (
                $token[ReflectionFile::TOKEN_TYPE] == ')'
                || $token[ReflectionFile::TOKEN_TYPE] == ']'
            ) {
                --$level;
                $definition['value'] .= $token[ReflectionFile::TOKEN_CODE];
                continue;
            }

            if ($level) {
                $definition['value'] .= $token[ReflectionFile::TOKEN_CODE];
                continue;
            }

            if ($token[ReflectionFile::TOKEN_TYPE] == ',') {
                $result[] = self::createArgument($definition);
                $definition = null;
                continue;
            }

            $definition['tokens'][] = $token;
            $definition['value'] .= $token[ReflectionFile::TOKEN_CODE];
        }

        //Last argument
        if (is_array($definition)) {
            $definition = self::createArgument($definition);
            if (!empty($definition->getType())) {
                $result[] = $definition;
            }
        }

        return $result;
    }

    /**
     * Create Argument reflection using token definition. Internal method.
     *
     * @see locateArguments
     */
    private static function createArgument(array $definition): ReflectionArgument
    {
        $result = new static(self::EXPRESSION, $definition['value']);

        if (count($definition['tokens']) == 1) {
            //If argument represent by one token we can try to resolve it's type more precisely
            switch ($definition['tokens'][0][0]) {
                case T_VARIABLE:
                    $result->type = self::VARIABLE;
                    break;
                case T_LNUMBER:
                case T_DNUMBER:
                    $result->type = self::CONSTANT;
                    break;
                case T_CONSTANT_ENCAPSED_STRING:
                    $result->type = self::STRING;
                    break;
            }
        }

        return $result;
    }
}
