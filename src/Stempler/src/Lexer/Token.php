<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

final class Token
{
    // Does not any type, raw user content.
    public const TYPE_RAW = 0;

    public array $tokens = [];

    public function __construct(
        public int $type,
        public ?int $offset,
        public string $content,
        public ?string $grammar = null
    ) {
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
            $token['type'] = \call_user_func([$this->grammar, 'tokenName'], $this->type);
        }

        if ($this->tokens !== []) {
            $token['tokens'] = $this->tokens;
        }

        return $token;
    }
}
