<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer\Grammar\Dynamic;

use Spiral\Stempler\Lexer\Buffer;
use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Grammar\Traits\TokenTrait;
use Spiral\Stempler\Lexer\Token;

/**
 * Control open and close brace grammar.
 */
final class BracesGrammar
{
    use TokenTrait;

    private bool $active = true;
    private ?string $startSequence = null;
    private ?string $endSequence = null;
    private array $body = [];

    public function __construct(
        string $startSequence,
        string $endSequence,
        private readonly int $startToken,
        private readonly int $endToken
    ) {
        $this->setStartSequence($startSequence);
        $this->setEndSequence($endSequence);
    }

    /**
     * Disable braces grammar.
     */
    public function disable(): void
    {
        $this->active = false;
    }

    /**
     * Enable braces grammar.
     */
    public function enable(): void
    {
        $this->active = true;
    }

    public function setStartSequence(string $startSequence): void
    {
        $this->startSequence = $startSequence;
    }

    public function setEndSequence(string $endSequence): void
    {
        $this->endSequence = $endSequence;
    }

    /**
     * Enable to disable grammar.
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function nextToken(Buffer $src): bool
    {
        return $this->active && $src->lookaheadByte(\strlen((string) $this->startSequence)) === $this->startSequence;
    }

    /**
     * Check if braces starts here.
     */
    public function starts(Buffer $src, Byte $n): bool
    {
        if (!$this->active) {
            return false;
        }

        return $this->startSequence === ($n->char . $src->lookaheadByte(\strlen((string) $this->startSequence) - 1));
    }

    /**
     * Parse braces content and return generated tokens or null in case of error.
     */
    public function parse(Buffer $src, Byte $n): ?array
    {
        $this->tokens = [
            new Token(
                $this->startToken,
                $n->offset,
                $n->char . $this->nextBytes($src, \strlen((string) $this->startSequence) - 1)
            ),
        ];

        while ($n = $src->next()) {
            if (!$n instanceof Byte) {
                // no other grammars are allowed
                break;
            }

            switch ($n->char) {
                case '"':
                case "'":
                    $this->body[] = $n;
                    while ($nn = $src->next()) {
                        $this->body[] = $nn;
                        if ($nn instanceof Byte && $nn->char === $n->char) {
                            break;
                        }
                    }
                    break;

                case $this->endSequence[0]:
                    if (!$this->ends($src, $n)) {
                        // still part of body
                        $this->body[] = $n;
                        break;
                    }

                    $this->flushBody();
                    $this->tokens[] = new Token(
                        $this->endToken,
                        $n->offset,
                        $n->char . $this->nextBytes($src, \strlen((string) $this->endSequence) - 1)
                    );

                    break 2;
                default:
                    $this->body[] = $n;
            }
        }

        if (\count($this->tokens) !== 3) {
            return null;
        }

        return $this->tokens;
    }

    /**
     * Check if braces ends here.
     */
    private function ends(Buffer $src, Byte $n): bool
    {
        return $this->endSequence === ($n->char . $src->lookaheadByte(\strlen((string) $this->endSequence) - 1));
    }

    /**
     * Fetch next N bytes.
     *
     * TODO issue #767
     * @link https://github.com/spiral/framework/issues/767
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function nextBytes(Buffer $src, int $size): string
    {
        $result = '';
        for ($i = 0; $i < $size; $i++) {
            $result .= $src->next()->char;
        }

        return $result;
    }

    /**
     * Pack name token.
     */
    private function flushBody(): void
    {
        if ($this->body === []) {
            return;
        }

        $this->tokens[] = $this->packToken($this->body, DynamicGrammar::TYPE_BODY);
        $this->body = [];
    }
}
