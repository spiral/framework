<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer\Grammar;

use Spiral\Stempler\Lexer\Buffer;
use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\Grammar\Traits\TokenTrait;
use Spiral\Stempler\Lexer\GrammarInterface;
use Spiral\Stempler\Lexer\Token;

/**
 * @see https://html.spec.whatwg.org/multipage/syntax.htm
 */
final class HTMLGrammar implements GrammarInterface
{
    use TokenTrait;

    // HTML grammar tokens
    public const TYPE_RAW         = 0;
    public const TYPE_KEYWORD     = 1;
    public const TYPE_OPEN        = 2;
    public const TYPE_OPEN_SHORT  = 3;
    public const TYPE_CLOSE       = 4;
    public const TYPE_CLOSE_SHORT = 5;
    public const TYPE_EQUAL       = 6;
    public const TYPE_ATTRIBUTE   = 7;
    public const TYPE_WHITESPACE  = 9;
    public const TYPE_VERBATIM    = 10;

    // Content within given tags must not be parsed
    private const VERBATIM_TAGS = ['script', 'canvas', 'style'];

    // whitespace
    private const REGEXP_WHITESPACE = '/\\s/';

    // Allowed keyword characters.
    private const REGEXP_KEYWORD = '/[a-z0-9_\\-:\\.]/ui';

    private array $whitespace = [];
    /**
     * @var array<array-key, Byte|Token>|array{0: Byte}
     */
    private array $attribute = [];
    private array $keyword = [];

    public function parse(Buffer $src): \Generator
    {
        while ($n = $src->next()) {
            if (!$n instanceof Byte || $n->char !== '<') {
                yield $n;
                continue;
            }

            // work with isolated token stream!
            $tag = (clone $this)->parseGrammar($src);
            if ($tag === null) {
                yield $n;
                $src->replay($n->offset);
                continue;
            }

            $tagName = $this->tagName($tag);

            // todo: add support for custom tag list
            if (\in_array($tagName, self::VERBATIM_TAGS)) {
                yield from $tag;
                yield from $this->parseVerbatim($src, $tagName);
                continue;
            }

            yield from $tag;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public static function tokenName(int $token): string
    {
        return match ($token) {
            self::TYPE_RAW => 'HTML:RAW',
            self::TYPE_KEYWORD => 'HTML:KEYWORD',
            self::TYPE_OPEN => 'HTML:OPEN_TAG',
            self::TYPE_OPEN_SHORT => 'HTML:OPEN_SHORT_TAG',
            self::TYPE_CLOSE => 'HTML:CLOSE_TAG',
            self::TYPE_CLOSE_SHORT => 'HTML:CLOSE_SHORT_TAG',
            self::TYPE_EQUAL => 'HTML:EQUAL',
            self::TYPE_ATTRIBUTE => 'HTML:ATTRIBUTE',
            self::TYPE_WHITESPACE => 'HTML:WHITESPACE',
            self::TYPE_VERBATIM => 'HTML:VERBATIM',
            default => 'HTML:UNDEFINED',
        };
    }

    private function parseVerbatim(Buffer $src, string $verbatim): \Generator
    {
        $chunks = [];

        while ($n = $src->next()) {
            if ($n instanceof Token) {
                $chunks[] = $n;
                continue;
            }

            switch ($n->char) {
                case '"':
                case "'":
                case '`':
                    $chunks[] = $n;

                    // language inclusions allow nested strings
                    while ($nc = $src->next()) {
                        $chunks[] = $nc;
                        if ($nc instanceof Token) {
                            continue;
                        }

                        if ($nc->char === $n->char) {
                            break;
                        }
                    }

                    break;

                case '/':
                    $chunks[] = $n;

                    $multiline = false;
                    if ($src->lookaheadByte(1) === '/' || $src->lookaheadByte(1) === '*') {
                        if ($src->lookaheadByte(1) === '*') {
                            $multiline = true;
                        }

                        $chunks[] = $src->next();

                        // language inclusions allow nested strings
                        while ($nc = $src->next()) {
                            if ($nc instanceof Token) {
                                continue;
                            }

                            if ($nc->char === '<') {
                                $tag = (clone $this)->parseGrammar($src);
                                if ($tag === null || $this->tagName($tag) !== $verbatim) {
                                    $src->replay($n->offset);
                                    break;
                                }
                                // back to primary loop
                                $src->replay($nc->offset - 1);
                                break 2;
                            }

                            $chunks[] = $nc;

                            if ($multiline) {
                                if ($nc->char === '*' && $src->lookaheadByte(1) === '/') {
                                    $chunks[] = $src->next();
                                    break;
                                }
                            } elseif ($nc->char === "\n") {
                                break;
                            }
                        }
                    }

                    break;

                case '<':
                    // tag beginning?
                    $tag = (clone $this)->parseGrammar($src);
                    if ($tag === null || $this->tagName($tag) !== $verbatim) {
                        $chunks[] = $n;
                        $src->replay($n->offset);
                        break;
                    }

                    // found closing verbatim tag
                    yield $this->packToken($chunks, self::TYPE_VERBATIM);
                    yield from $tag;

                    break 2;

                default:
                    $chunks[] = $n;
            }
        }
    }

    private function tagName(array $tag): string
    {
        foreach ($tag as $token) {
            if ($token->type === self::TYPE_KEYWORD) {
                return \strtolower($token->content);
            }
        }

        return '';
    }

    /**
     * TODO issue #767
     * @link https://github.com/spiral/framework/issues/767
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function parseGrammar(Buffer $src): ?array
    {
        $this->tokens = [
            new Token(self::TYPE_OPEN, $src->getOffset(), '<'),
        ];

        if ($src->lookaheadByte() === '/') {
            $this->tokens[0]->type = self::TYPE_OPEN_SHORT;
            $this->tokens[0]->content .= $src->next()->char;
        }

        while ($n = $src->next()) {
            if ($this->attribute !== []) {
                $this->attribute[] = $n;

                if ($n instanceof Byte && $n->char === $this->attribute[0]->char) {
                    $this->flushAttribute();
                }

                continue;
            }

            if ($n instanceof Token) {
                $this->keyword[] = $n;
                continue;
            }

            switch ($n->char) {
                case '"':
                case "'":
                case '`':
                    $this->flush();
                    $this->attribute[] = $n;
                    break;

                case '=':
                    $this->flush();
                    $this->tokens[] = new Token(
                        self::TYPE_EQUAL,
                        $n->offset,
                        $n->char
                    );
                    break;

                case '/':
                    if ($src->lookaheadByte() === '>') {
                        $this->flush();
                        $this->tokens[] = new Token(
                            self::TYPE_CLOSE_SHORT,
                            $n->offset,
                            $n->char . $src->next()->char
                        );

                        break 2;
                    }

                    // unexpected "/"
                    return null;

                case '>':
                    $this->flush();
                    $this->tokens[] = new Token(
                        self::TYPE_CLOSE,
                        $n->offset,
                        $n->char
                    );
                    break 2;

                default:
                    if (\preg_match(self::REGEXP_WHITESPACE, $n->char)) {
                        $this->flushKeyword();
                        $this->whitespace[] = $n;
                        break;
                    }
                    $this->flushWhitespace();


                    if (!\preg_match(self::REGEXP_KEYWORD, $n->char)) {
                        // unexpected char
                        return null;
                    }

                    $this->keyword[] = $n;
            }
        }

        if (!$this->isValid()) {
            return null;
        }

        return $this->tokens;
    }

    private function isValid(): bool
    {
        // tag is too short or does not have name keyword
        if (\count($this->tokens) < 3) {
            return false;
        }

        $last = $this->tokens[\count($this->tokens) - 1];
        if ($last->type !== self::TYPE_CLOSE && $last->type !== self::TYPE_CLOSE_SHORT) {
            return false;
        }

        foreach ($this->tokens as $token) {
            switch ($token->type) {
                case self::TYPE_WHITESPACE:
                    // ignore
                    continue 2;

                case self::TYPE_ATTRIBUTE:
                case self::TYPE_EQUAL:
                    return false;

                case self::TYPE_KEYWORD:
                    return true;
            }
        }

        return false;
    }

    /**
     * Flush whitespace or keyword tokens.
     */
    private function flush(): void
    {
        $this->flushWhitespace();
        $this->flushKeyword();
    }

    /**
     * Flush keyword content.
     */
    private function flushWhitespace(): void
    {
        if ($this->whitespace === []) {
            return;
        }

        $this->tokens[] = $this->packToken($this->whitespace, self::TYPE_WHITESPACE);
        $this->whitespace = [];
    }

    /**
     * Flush keyword content.
     */
    private function flushKeyword(): void
    {
        if ($this->keyword === []) {
            return;
        }

        $this->tokens[] = $this->packToken($this->keyword, self::TYPE_KEYWORD);
        $this->keyword = [];
    }

    /**
     * Flush attribute content.
     */
    private function flushAttribute(): void
    {
        if ($this->attribute === []) {
            return;
        }

        $this->tokens[] = $this->packToken($this->attribute, self::TYPE_ATTRIBUTE);
        $this->attribute = [];
    }
}
