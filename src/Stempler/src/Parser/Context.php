<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser;

use Spiral\Stempler\Lexer\Token;

/**
 * Defines the node location in a source code.
 */
final class Context
{
    public ?Context $parent = null;
    private array $values = [];

    public function __construct(
        private Token $token,
        private ?string $path = null
    ) {
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function withValue(string $name, mixed $value): self
    {
        $ctx = clone $this;
        $ctx->values[$name] = $value;

        return $ctx;
    }

    public function getValue(string $name, mixed $default = null): mixed
    {
        return $this->values[$name] ?? $default;
    }
}
