<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

final class Token
{
    // Does not any type, raw user content.
    public const TYPE_RAW = 0;

    /** @var string */
    public $grammar;

    /** @var int */
    public $type;

    /** @var int */
    public $offset;

    /** @var string */
    public $content;

    /** @var array */
    public $tokens = [];

    /**
     * @param int         $type
     * @param int         $offset
     * @param string      $content
     * @param string|null $grammar
     */
    public function __construct(int $type, int $offset, string $content, string $grammar = null)
    {
        $this->type = $type;
        $this->offset = $offset;
        $this->content = $content;
        $this->grammar = $grammar;
    }

    /**
     * User friendly token information.
     *
     * @codeCoverageIgnore
     */
    public function __debugInfo(): array
    {
        $token = [
            'type'    => $this->type,
            'offset'  => $this->offset,
            'content' => $this->content,
        ];

        if ($this->grammar !== null) {
            $token['type'] = call_user_func([$this->grammar, 'tokenName'], $this->type);
        }

        if ($this->tokens !== []) {
            $token['tokens'] = $this->tokens;
        }

        return $token;
    }
}
