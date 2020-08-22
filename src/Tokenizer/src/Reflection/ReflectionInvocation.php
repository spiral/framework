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
 * ReflectionInvocation used to represent function or static method call found by ReflectionFile.
 * This reflection is very useful for static analysis and mainly used in Translator component to
 * index translation function usages.
 */
final class ReflectionInvocation
{
    /** @var string */
    private $filename = '';

    /** @var int */
    private $line = 0;

    /** @var string */
    private $class = '';

    /** @var string */
    private $operator = '';

    /** @var string */
    private $name = '';

    /** @var string */
    private $source = '';

    /** @var ReflectionArgument[] */
    private $arguments = [];

    /**
     * Was a function used inside another function call?
     *
     * @var int
     */
    private $level = 0;

    /**
     * New call reflection.
     *
     * @param string               $filename
     * @param int                  $line
     * @param string               $class
     * @param string               $operator
     * @param string               $name
     * @param ReflectionArgument[] $arguments
     * @param string               $source
     * @param int                  $level
     */
    public function __construct(
        string $filename,
        int $line,
        string $class,
        string $operator,
        string $name,
        array $arguments,
        string $source,
        int $level
    ) {
        $this->filename = $filename;
        $this->line = $line;
        $this->class = $class;
        $this->operator = $operator;
        $this->name = $name;
        $this->arguments = $arguments;
        $this->source = $source;
        $this->level = $level;
    }

    /**
     * Function usage filename.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return str_replace('\\', '/', $this->filename);
    }

    /**
     * Function usage line.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Parent class.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Method operator (:: or ->).
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Function or method name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Call made by class method.
     *
     * @return bool
     */
    public function isMethod(): bool
    {
        return !empty($this->class);
    }

    /**
     * Function usage src.
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Count of arguments in call.
     *
     * @return int
     */
    public function countArguments(): int
    {
        return count($this->arguments);
    }

    /**
     * All parsed function arguments.
     *
     * @return ReflectionArgument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get call argument by it's position.
     *
     * @param int $index
     *
     * @return ReflectionArgument|null
     */
    public function getArgument(int $index): ReflectionArgument
    {
        if (!isset($this->arguments[$index])) {
            throw new ReflectionException("No such argument with index '{$index}'");
        }

        return $this->arguments[$index];
    }

    /**
     * Invoking level.
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }
}
