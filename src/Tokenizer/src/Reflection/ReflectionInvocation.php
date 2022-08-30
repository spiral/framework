<?php

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
    /**
     * New call reflection.
     *
     * @param class-string $class
     * @param ReflectionArgument[] $arguments
     * @param int $level Was a function used inside another function call?
     */
    public function __construct(
        private readonly string $filename,
        private readonly int $line,
        private readonly string $class,
        private readonly string $operator,
        private readonly string $name,
        private readonly array $arguments,
        private readonly string $source,
        private readonly int $level
    ) {
    }

    /**
     * Function usage filename.
     */
    public function getFilename(): string
    {
        return \str_replace('\\', '/', $this->filename);
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
     *
     * @return class-string
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
        return \count($this->arguments);
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
     * Get call argument by it position.
     */
    public function getArgument(int $index): ReflectionArgument
    {
        if (!isset($this->arguments[$index])) {
            throw new ReflectionException(\sprintf("No such argument with index '%d'", $index));
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
