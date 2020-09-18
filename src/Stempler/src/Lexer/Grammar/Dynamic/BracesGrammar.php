<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /** @var bool */
    private $active;

    /** @var int */
    private $startToken;

    /** @var int */
    private $endToken;

    /** @var string */
    private $startSequence;

    /** @var string */
    private $endSequence;

    /** @var array */
    private $body = [];

    /**
     * @param string $startSequence
     * @param string $endSequence
     * @param int    $startToken
     * @param int    $closeToken
     */
    public function __construct(string $startSequence, string $endSequence, int $startToken, int $closeToken)
    {
        $this->active = true;

        $this->startToken = $startToken;
        $this->endToken = $closeToken;

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

    /**
     * @param string $startSequence
     */
    public function setStartSequence(string $startSequence): void
    {
        $this->startSequence = $startSequence;
    }

    /**
     * @param string $endSequence
     */
    public function setEndSequence(string $endSequence): void
    {
        $this->endSequence = $endSequence;
    }

    /**
     * Enable to disable grammar.
     *
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @param Buffer $src
     * @return bool
     */
    public function nextToken(Buffer $src): bool
    {
        return $this->active && $src->lookaheadByte(strlen($this->startSequence)) === $this->startSequence;
    }

    /**
     * Check if braces starts here.
     *
     * @param Buffer $src
     * @param Byte   $n
     * @return bool
     */
    public function starts(Buffer $src, Byte $n): bool
    {
        if (!$this->active) {
            return false;
        }

        return $this->startSequence === ($n->char . $src->lookaheadByte(strlen($this->startSequence) - 1));
    }

    /**
     * Parse braces content and return generated tokens or null in case of error.
     *
     * @param Buffer $src
     * @param Byte   $n
     * @return array|null
     */
    public function parse(Buffer $src, Byte $n): ?array
    {
        $this->tokens = [
            new Token(
                $this->startToken,
                $n->offset,
                $n->char . $this->nextBytes($src, strlen($this->startSequence) - 1)
            )
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
                        $n->char . $this->nextBytes($src, strlen($this->endSequence) - 1)
                    );

                    break 2;
                default:
                    $this->body[] = $n;
            }
        }

        if (count($this->tokens) !== 3) {
            return null;
        }

        return $this->tokens;
    }

    /**
     * Check if braces ends here.
     *
     * @param Buffer $src
     * @param Byte   $n
     * @return bool
     */
    private function ends(Buffer $src, Byte $n): bool
    {
        return $this->endSequence === ($n->char . $src->lookaheadByte(strlen($this->endSequence) - 1));
    }

    /**
     * Fetch next N bytes.
     *
     * @param Buffer $src
     * @param int    $size
     * @return string
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
