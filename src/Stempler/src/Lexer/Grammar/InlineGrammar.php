<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Lexer\Grammar;

use Spiral\Stempler\Lexer\Buffer;
use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\Grammar\Traits\TokenTrait;
use Spiral\Stempler\Lexer\GrammarInterface;
use Spiral\Stempler\Lexer\Token;

/**
 * Handle block inline injects ${name|default}, to be used in combination with HTML grammar.
 */
final class InlineGrammar implements GrammarInterface
{
    use TokenTrait;

    // inject grammar tokens
    public const TYPE_OPEN_TAG  = 1;
    public const TYPE_CLOSE_TAG = 2;
    public const TYPE_NAME      = 3;
    public const TYPE_SEPARATOR = 4;
    public const TYPE_DEFAULT   = 5;

    // whitespace
    private const REGEXP_WHITESPACE = '/\s/';

    // Allowed keyword characters.
    private const REGEXP_KEYWORD = '/[a-z0-9_\-:\.]/ui';

    /** @var Byte[] */
    private $name = [];

    /** @var Byte[]|null */
    private $default = null;

    /**
     * @inheritDoc
     */
    public function parse(Buffer $src): \Generator
    {
        while ($n = $src->next()) {
            if (!$n instanceof Byte || $n->char !== '$' || $src->lookaheadByte() !== '{') {
                yield $n;
                continue;
            }

            $binding = (clone $this)->parseGrammar($src, $n->offset);
            if ($binding === null) {
                yield $n;
                $src->replay($n->offset);
                continue;
            }

            yield from $binding;
        }
    }

    /**
     * @codeCoverageIgnore
     * @inheritDoc
     */
    public static function tokenName(int $token): string
    {
        switch ($token) {
            case self::TYPE_OPEN_TAG:
                return 'INLINE:OPEN_TAG';
            case self::TYPE_CLOSE_TAG:
                return 'INLINE:CLOSE_TAG';
            case self::TYPE_NAME:
                return 'INLINE:NAME';
            case self::TYPE_SEPARATOR:
                return 'INLINE:SEPARATOR';
            case self::TYPE_DEFAULT:
                return 'INLINE:DEFAULT';
            default:
                return 'INLINE:UNDEFINED';
        }
    }

    /**
     * @param Buffer $src
     * @param int    $offset
     * @return Token[]|null
     */
    private function parseGrammar(Buffer $src, int $offset): ?array
    {
        $this->tokens = [
            new Token(self::TYPE_OPEN_TAG, $offset, '$' . $src->next()->char),
        ];

        while ($n = $src->next()) {
            if (!$n instanceof Byte) {
                // no other grammars are allowed
                return null;
            }

            switch ($n->char) {
                case '"':
                case "'":
                    if ($this->default === null) {
                        // " and ' not allowed in names
                        return null;
                    }

                    $this->default[] = $n;
                    while ($nn = $src->next()) {
                        $this->default[] = $nn;
                        if ($nn instanceof Byte && $nn->char === $n->char) {
                            break;
                        }
                    }
                    break;

                case '}':
                    $this->flushName();
                    $this->flushDefault();

                    $this->tokens[] = new Token(
                        self::TYPE_CLOSE_TAG,
                        $n->offset,
                        $n->char
                    );

                    break 2;

                case '|':
                    $this->flushName();
                    $this->flushDefault();

                    $this->tokens[] = new Token(
                        self::TYPE_SEPARATOR,
                        $n->offset,
                        $n->char
                    );

                    $this->default = [];

                    break;

                default:
                    if ($this->default !== null) {
                        // default allows spaces
                        $this->default[] = $n;
                        break;
                    }

                    if (preg_match(self::REGEXP_WHITESPACE, $n->char)) {
                        break;
                    }

                    if (preg_match(self::REGEXP_KEYWORD, $n->char)) {
                        $this->name[] = $n;
                        break;
                    }

                    return null;
            }
        }

        if (!$this->isValid()) {
            return null;
        }

        return $this->tokens;
    }

    /**
     * @return bool
     */
    private function isValid(): bool
    {
        if (count($this->tokens) < 3) {
            return false;
        }

        $hasName = false;
        $hasDefault = null;
        foreach ($this->tokens as $token) {
            if ($token->type === self::TYPE_NAME) {
                $hasName = true;
                continue;
            }

            if ($token->type === self::TYPE_SEPARATOR && $hasDefault === null) {
                $hasDefault = false;
                continue;
            }

            if ($token->type === self::TYPE_DEFAULT) {
                if ($hasDefault === true) {
                    // multiple default value
                    return false;
                }

                $hasDefault = true;
            }
        }

        return $hasName && ($hasDefault === null || $hasDefault === true);
    }

    /**
     * Pack name token.
     */
    private function flushName(): void
    {
        if ($this->name === []) {
            return;
        }

        $this->tokens[] = $this->packToken($this->name, self::TYPE_NAME);
        $this->name = [];
    }

    /**
     * Pack default token.
     */
    private function flushDefault(): void
    {
        if ($this->default === [] || $this->default === null) {
            return;
        }

        $this->tokens[] = $this->packToken($this->default, self::TYPE_DEFAULT);
        $this->default = [];
    }
}
