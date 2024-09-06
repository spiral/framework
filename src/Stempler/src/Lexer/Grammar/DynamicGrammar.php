<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer\Grammar;

use Spiral\Stempler\Directive\DirectiveRendererInterface;
use Spiral\Stempler\Lexer\Buffer;
use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\Grammar\Dynamic\BracesGrammar;
use Spiral\Stempler\Lexer\Grammar\Dynamic\DeclareGrammar;
use Spiral\Stempler\Lexer\Grammar\Dynamic\DirectiveGrammar;
use Spiral\Stempler\Lexer\Grammar\Traits\TokenTrait;
use Spiral\Stempler\Lexer\GrammarInterface;
use Spiral\Stempler\Lexer\Lexer;
use Spiral\Stempler\Lexer\StringStream;
use Spiral\Stempler\Lexer\Token;

/**
 * Similar to Laravel blade, this grammar defines the ability to echo PHP variables using {{ $var }} statements
 * Grammar also support various component support using [@directive(options)] pattern.
 *
 * Attention, the syntaxt will treat all [@sequnce()] as directive unless DirectiveRendererInterface is provided.
 */
final class DynamicGrammar implements GrammarInterface
{
    use TokenTrait;

    // inject grammar tokens
    public const TYPE_OPEN_TAG      = 1;
    public const TYPE_CLOSE_TAG     = 2;
    public const TYPE_OPEN_RAW_TAG  = 3;
    public const TYPE_CLOSE_RAW_TAG = 4;
    public const TYPE_BODY_OPEN     = 5;
    public const TYPE_BODY_CLOSE    = 6;
    public const TYPE_BODY          = 7;
    public const TYPE_DIRECTIVE     = 8;
    public const TYPE_KEYWORD       = 9;
    public const TYPE_WHITESPACE    = 10;

    // grammar control directive
    public const DECLARE_DIRECTIVE = 'declare';

    private readonly BracesGrammar $echo;
    private readonly BracesGrammar $raw;

    public function __construct(
        private readonly ?DirectiveRendererInterface $directiveRenderer = null
    ) {
        $this->echo = new BracesGrammar(
            '{{',
            '}}',
            self::TYPE_OPEN_TAG,
            self::TYPE_CLOSE_TAG
        );

        $this->raw = new BracesGrammar(
            '{!!',
            '!!}',
            self::TYPE_OPEN_RAW_TAG,
            self::TYPE_CLOSE_RAW_TAG
        );
    }

    /**
     * @return \Generator<int, Byte|Token|null>
     */
    public function parse(Buffer $src): \Generator
    {
        while ($n = $src->next()) {
            if (!$n instanceof Byte) {
                yield $n;
                continue;
            }

            if ($n->char === DirectiveGrammar::DIRECTIVE_CHAR) {
                if (
                    $this->echo->nextToken($src) ||
                    $this->raw->nextToken($src) ||
                    $src->lookaheadByte() === DirectiveGrammar::DIRECTIVE_CHAR
                ) {
                    // escaped echo sequence, hide directive byte
                    yield $src->next();
                    continue;
                }

                $directive = new DirectiveGrammar();
                if ($directive->parse($src, $n->offset)) {
                    if (\strtolower($directive->getKeyword()) === self::DECLARE_DIRECTIVE) {
                        // configure braces syntax
                        $this->declare($directive->getBody());
                    } else {
                        if (
                            $this->directiveRenderer !== null
                            && !$this->directiveRenderer->hasDirective($directive->getKeyword())
                        ) {
                            // directive opening char
                            yield $n;

                            // unknown directive, treat as plain test
                            $src->replay($n->offset);
                            continue;
                        }

                        yield from $directive;
                    }

                    $src->replay($directive->getLastOffset());
                    continue;
                }

                $src->replay($n->offset);
            }

            /** @var BracesGrammar|null $braces */
            $braces = null;
            if ($this->echo->starts($src, $n)) {
                $braces = clone $this->echo;
            } elseif ($this->raw->starts($src, $n)) {
                $braces = clone $this->raw;
            }

            if ($braces !== null) {
                $echo = $braces->parse($src, $n);
                if ($echo !== null) {
                    yield from $echo;
                    continue;
                }

                $src->replay($n->offset);
            }

            yield $n;
        }

        yield from $src;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function tokenName(int $token): string
    {
        return match ($token) {
            self::TYPE_OPEN_TAG => 'DYNAMIC:OPEN_TAG',
            self::TYPE_CLOSE_TAG => 'DYNAMIC:CLOSE_TAG',
            self::TYPE_OPEN_RAW_TAG => 'DYNAMIC:OPEN_RAW_TAG',
            self::TYPE_CLOSE_RAW_TAG => 'DYNAMIC:CLOSE_RAW_TAG',
            self::TYPE_BODY => 'DYNAMIC:BODY',
            self::TYPE_DIRECTIVE => 'DYNAMIC:DIRECTIVE',
            self::TYPE_KEYWORD => 'DYNAMIC:KEYWORD',
            self::TYPE_WHITESPACE => 'DYNAMIC:WHITESPACE',
            default => 'DYNAMIC:UNDEFINED',
        };
    }

    private function declare(?string $body): void
    {
        if ($body === null) {
            return;
        }

        foreach ($this->fetchOptions($body) as $option => $value) {
            $value = \trim((string) $value, '\'" ');
            switch ($option) {
                case 'syntax':
                    $this->echo->setActive($value !== 'off');
                    $this->raw->setActive($value !== 'off');

                    if ($value === 'default') {
                        $this->echo->setStartSequence('{{');
                        $this->echo->setEndSequence('}}');
                        $this->raw->setStartSequence('{!!');
                        $this->raw->setStartSequence('!!}');
                    }
                    break;

                case 'open':
                    $this->echo->setStartSequence($value);
                    break;

                case 'close':
                    $this->echo->setEndSequence($value);
                    break;

                case 'openRaw':
                    $this->raw->setStartSequence($value);
                    break;

                case 'closeRaw':
                    $this->raw->setEndSequence($value);
                    break;
            }
        }
    }

    private function fetchOptions(string $body): array
    {
        $lexer = new Lexer();
        $lexer->addGrammar(new DeclareGrammar());

        // generated options
        $options = [];
        $keyword = null;

        /** @var Token $token */
        foreach ($lexer->parse(new StringStream($body)) as $token) {
            switch ($token->type) {
                case DeclareGrammar::TYPE_KEYWORD:
                    if ($keyword !== null) {
                        $options[$keyword] = $token->content;
                        $keyword = null;
                        break;
                    }
                    $keyword = $token->content;
                    break;
                case DeclareGrammar::TYPE_QUOTED:
                    if ($keyword !== null) {
                        $options[$keyword] = \trim($token->content, $token->content[0]);
                        $keyword = null;
                        break;
                    }

                    $keyword = \trim($token->content, $token->content[0]);
                    break;
                case DeclareGrammar::TYPE_COMMA:
                    if ($keyword !== null) {
                        $options[$keyword] = null;
                        $keyword = null;
                        break;
                    }
            }
        }

        return $options;
    }
}
