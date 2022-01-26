<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Lexer\Grammar\Dynamic;

use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Lexer\Buffer;
use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Grammar\Traits\TokenTrait;
use Spiral\Stempler\Lexer\Token;

final class DirectiveGrammar implements \IteratorAggregate
{
    use TokenTrait;

    // start directive
    public const DIRECTIVE_CHAR = '@';

    // whitespace
    private const REGEXP_WHITESPACE = '/\s/';

    // Allowed keyword characters.
    private const REGEXP_KEYWORD = '/[a-z0-9_\-:\.]/ui';

    /** @var array */
    private $name = [];

    /** @var array */
    private $body = [];

    public function parse(Buffer $src, int $offset): bool
    {
        $this->tokens = [
            new Token(DynamicGrammar::TYPE_DIRECTIVE, $offset, self::DIRECTIVE_CHAR),
        ];

        $this->body = null;
        $hasWhitespace = false;

        while ($n = $src->next()) {
            if (!$n instanceof Byte) {
                // no other grammars are allowed
                break;
            }

            switch ($n->char) {
                case '(':
                    $this->flushName();
                    $this->tokens[] = new Token(DynamicGrammar::TYPE_BODY_OPEN, $n->offset, $n->char);

                    return $this->parseBody($src);
                default:
                    if (preg_match(self::REGEXP_WHITESPACE, $n->char)) {
                        $hasWhitespace = true;
                        if ($this->name !== []) {
                            $this->flushName();
                            $this->tokens[] = new Token(DynamicGrammar::TYPE_WHITESPACE, $n->offset, $n->char);
                            break;
                        }

                        if ($this->getLastToken()->type === DynamicGrammar::TYPE_WHITESPACE) {
                            $this->getLastToken()->content .= $n->char;
                            break;
                        }

                        // invalid directive
                        return false;
                    } elseif ($hasWhitespace) {
                        return $this->finalize();
                    }

                    if (!preg_match(self::REGEXP_KEYWORD, $n->char)) {
                        $this->flushName();

                        return $this->finalize();
                    }

                    $this->name[] = $n;
            }
        }

        $this->flushName();

        return $this->finalize();
    }

    /**
     * Directive tokens.
     *
     * @return \Generator|\Traversable
     */
    public function getIterator(): \Traversable
    {
        if ($this->tokens === []) {
            throw new \LogicException('Directive not parsed');
        }

        yield from $this->tokens;
    }

    /**
     * Return offset after last directive token.
     */
    public function getLastOffset(): int
    {
        return $this->getLastToken()->offset + strlen($this->getLastToken()->content) - 1;
    }

    /**
     * Get directive keyword.
     */
    public function getKeyword(): string
    {
        foreach ($this->tokens as $token) {
            if ($token->type === DynamicGrammar::TYPE_KEYWORD) {
                return $token->content;
            }
        }

        throw new SyntaxException('Directive not parsed', $this->tokens[0]);
    }

    /**
     * Get directive body.
     *
     * @return string
     */
    public function getBody(): ?string
    {
        foreach ($this->tokens as $token) {
            if ($token->type === DynamicGrammar::TYPE_BODY) {
                return $token->content;
            }
        }

        return null;
    }

    /**
     * Pack keyword token.
     */
    private function flushName(): void
    {
        if ($this->name === []) {
            return;
        }

        $this->tokens[] = $this->packToken($this->name, DynamicGrammar::TYPE_KEYWORD);
        $this->name = [];
    }

    /**
     * @return bool
     */
    private function parseBody(Buffer $src)
    {
        $this->body = [];
        $level = 1;

        while ($nn = $src->next()) {
            if (!$nn instanceof Byte) {
                $this->flushBody();
                return $this->finalize();
            }

            if (in_array($nn->char, ['"', '"'])) {
                $this->body[] = $nn;
                while ($nnn = $src->next()) {
                    $this->body[] = $nnn;
                    if ($nnn instanceof Byte && $nnn->char === $nn->char) {
                        break;
                    }
                }
                continue;
            }

            $this->body[] = $nn;

            if ($nn->char === '(') {
                $level++;
                continue;
            }

            if ($nn->char === ')') {
                $level--;

                if ($level === 0) {
                    $n = array_pop($this->body);

                    $this->flushBody();
                    $this->tokens[] = new Token(DynamicGrammar::TYPE_BODY_CLOSE, $n->offset, $n->char);

                    return $this->finalize();
                }

                continue;
            }
        }

        return $this->finalize();
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

    private function getLastToken(): Token
    {
        if ($this->tokens === []) {
            throw new \LogicException('Directive not parsed');
        }

        return $this->tokens[count($this->tokens) - 1];
    }

    /**
     * Flush directive and seek buffer before last non WHITESPACE token.
     */
    private function finalize(): bool
    {
        $tokens = $this->tokens;

        foreach (array_reverse($tokens, true) as $i => $t) {
            if ($t->type !== DynamicGrammar::TYPE_WHITESPACE) {
                break;
            }

            unset($tokens[$i]);
        }

        $body = null;
        foreach ($tokens as $t) {
            if ($t->type === DynamicGrammar::TYPE_BODY_OPEN) {
                $body = false;
                continue;
            }

            if ($t->type === DynamicGrammar::TYPE_BODY_CLOSE) {
                $body = null;
                continue;
            }
        }

        if ($body !== null) {
            return false;
        }

        $this->tokens = $tokens;

        return true;
    }
}
