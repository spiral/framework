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
use Spiral\Stempler\Lexer\Grammar\Traits\TokenTrait;
use Spiral\Stempler\Lexer\GrammarInterface;
use Spiral\Stempler\Lexer\Token;

/**
 * Provides ability to parse complex declare options.
 */
final class DeclareGrammar implements GrammarInterface
{
    use TokenTrait;

    public const TYPE_KEYWORD = 0;
    public const TYPE_EQUAL   = 1;
    public const TYPE_COMMA   = 2;
    public const TYPE_QUOTED  = 3;

    // whitespace
    private const REGEXP_WHITESPACE = '/\s/';

    /** @var array */
    private $keyword = [];

    /**
     * @inheritDoc
     */
    public function parse(Buffer $src): \Generator
    {
        while ($n = $src->next()) {
            switch ($n->char) {
                case '"':
                case "'":
                    if ($this->keyword !== []) {
                        yield $this->packToken($this->keyword, self::TYPE_KEYWORD);
                        $this->keyword = [];
                    }

                    $quoted[] = $n;
                    while ($nn = $src->next()) {
                        $quoted[] = $nn;
                        if ($nn instanceof Byte && $nn->char === $n->char) {
                            break;
                        }
                    }

                    yield $this->packToken($quoted, self::TYPE_QUOTED);
                    $quoted = [];

                    break;
                case '=':
                    if ($this->keyword !== []) {
                        yield $this->packToken($this->keyword, self::TYPE_KEYWORD);
                        $this->keyword = [];
                    }

                    yield new Token(self::TYPE_EQUAL, $n->offset, '=');
                    break;
                case ',':
                    if ($this->keyword !== []) {
                        yield $this->packToken($this->keyword, self::TYPE_KEYWORD);
                        $this->keyword = [];
                    }

                    yield new Token(self::TYPE_COMMA, $n->offset, ',');
                    break;
                default:
                    if (preg_match(self::REGEXP_WHITESPACE, $n->char)) {
                        if ($this->keyword !== []) {
                            yield $this->packToken($this->keyword, self::TYPE_KEYWORD);
                            $this->keyword = [];
                        }

                        break;
                    }

                    $this->keyword[] = $n;
            }
        }

        if ($this->keyword !== []) {
            yield $this->packToken($this->keyword, self::TYPE_KEYWORD);
        }
    }

    /**
     * @inheritDoc
     */
    public static function tokenName(int $token): string
    {
        switch ($token) {
            case self::TYPE_KEYWORD:
                return 'DECLARE:KEYWORD';
            case self::TYPE_EQUAL:
                return 'DECLARE:EQUAL';
            case self::TYPE_COMMA:
                return 'DECLARE:COMMA';
            case self::TYPE_QUOTED:
                return 'DECLARE:QUOTED';
            default:
                return 'DECLARE:UNDEFINED';
        }
    }
}
