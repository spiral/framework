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
     * @param ReflectionArgument[] $arguments
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
     */
    public function getFilename(): string
    {
        return str_replace('\\', '/', $this->filename);
    }

    /**
     * Function usage line.
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Parent class.
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Method operator (:: or ->).
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Function or method name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Call made by class method.
     */
    public function isMethod(): bool
    {
        return !empty($this->class);
    }

    /**
     * Function usage src.
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Count of arguments in call.
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
     */
    public function getLevel(): int
    {
        return $this->level;
    }
}
