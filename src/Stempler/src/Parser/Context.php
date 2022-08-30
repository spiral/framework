<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Parser;

use Spiral\Stempler\Lexer\Token;

/**
 * Defines the node location in a source code.
 */
final class Context
{
    public $parent;
    /** @var Token */
    private $token;

    /** @var string|null */
    private $path;

    /** @var array */
    private $values = [];

    /**
     * @param string|null $path
     */
    public function __construct(Token $token, string $path = null)
    {
        $this->token = $token;
        $this->path = $path;
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param mixed  $value
     */
    public function withValue(string $name, $value): self
    {
        $ctx = clone $this;
        $ctx->values[$name] = $value;
        return $ctx;
    }

    /**
     * @param null   $default
     * @return mixed|null
     */
    public function getValue(string $name, $default = null)
    {
        return $this->values[$name] ?? $default;
    }
}
