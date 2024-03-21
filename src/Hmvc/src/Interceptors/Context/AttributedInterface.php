<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Context;

interface AttributedInterface
{
    /**
     * Retrieve attributes derived from the context.
     *
     * The context "attributes" may be used to allow injection of any
     * parameters Attributes will be application- and context-specific, and CAN be mutable.
     *
     * @return array<non-empty-string, mixed> Attributes derived from the context.
     */
    public function getAttributes(): array;

    /**
     * Retrieve a single derived context attribute.
     *
     * Retrieves a single derived context attribute as described in {@see getAttributes()}.
     * If the attribute has not been previously set, returns the default value as provided.
     *
     * This method obviates the need for a {@see hasAttribute()} method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param non-empty-string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     */
    public function getAttribute(string $name, mixed $default = null): mixed;

    /**
     * Return an instance with the specified attribute.
     *
     * This method allows setting a single context attribute as
     * described in {@see getAttributes()}.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param non-empty-string $name The attribute name.
     * @param mixed $value The value of the attribute.
     */
    public function withAttribute(string $name, mixed $value): static;

    /**
     * Return an instance that removes the specified context attribute.
     *
     * This method allows removing a single context attribute as described in {@see getAttributes()}.
     *
     * @param non-empty-string $name The attribute name.
     */
    public function withoutAttribute(string $name): static;
}
