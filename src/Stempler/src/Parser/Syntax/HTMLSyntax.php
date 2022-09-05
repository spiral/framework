<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser\Syntax;

use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Nil;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\HTML\Verbatim;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser;
use Spiral\Stempler\Parser\Assembler;
use Spiral\Stempler\Parser\SyntaxInterface;

/**
 * HTML tags and attributes.
 */
final class HTMLSyntax implements SyntaxInterface
{
    use Parser\Syntax\Traits\MixinTrait;

    // list of tags which are closed automatically (http://xahlee.info/js/html5_non-closing_tag.html)
    private const VOID_TAGS = [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    ];

    // list of attributes which define verbatim blocks (https://www.w3schools.com/tags/ref_eventattributes.asp)
    private const VERBATIM_ATTRIBUTES = [
        'style',
        'on*',
    ];

    private ?Tag $node = null;
    private ?Token $token = null;
    private ?Attr $attr = null;

    public function handle(Parser $parser, Assembler $asm, Token $token): void
    {
        switch ($token->type) {
            case HTMLGrammar::TYPE_OPEN:
            case HTMLGrammar::TYPE_OPEN_SHORT:
                $this->node = new Tag(new Parser\Context($token, $parser->getPath()));
                $this->token = $token;

                break;

            case HTMLGrammar::TYPE_KEYWORD:
                if ($this->node->name === null) {
                    $this->node->name = $this->parseToken($parser, $token);
                    return;
                }

                if ($this->attr !== null && !$this->attr->value instanceof Nil) {
                    $this->attr->value = $this->parseToken($parser, $token);
                    $this->attr = null;
                    break;
                }

                $this->attr = new Attr(
                    $this->parseToken($parser, $token),
                    new Nil(),
                    new Parser\Context($token, $parser->getPath())
                );

                $this->node->attrs[] = $this->attr;
                break;

            case HTMLGrammar::TYPE_EQUAL:
                if ($this->attr === null) {
                    throw new SyntaxException('unexpected attribute token', $token);
                }

                // expect the value
                $this->attr->value = null;
                break;

            case HTMLGrammar::TYPE_ATTRIBUTE:
                if ($this->attr === null) {
                    throw new SyntaxException('unexpected attribute token', $token);
                }

                if (
                    \is_string($this->attr->name)
                    && (
                        \str_starts_with($this->attr->name, 'on')
                        || \in_array($this->attr->name, self::VERBATIM_ATTRIBUTES, true)
                    )
                ) {
                    $this->attr->value = $this->parseVerbatim($parser, $token);
                } else {
                    $this->attr->value = $this->parseToken($parser, $token);
                }

                $this->attr = null;
                break;

            case HTMLGrammar::TYPE_CLOSE_SHORT:
                $this->node->void = true;
                $asm->push($this->node);
                $this->flush();
                break;

            case HTMLGrammar::TYPE_CLOSE:
                if ($this->token->type == HTMLGrammar::TYPE_OPEN_SHORT) {
                    if (!$asm->getNode() instanceof Tag || $asm->getNode()->name !== $this->node->name) {
                        /**
                         * TODO issue #767
                         * @link https://github.com/spiral/framework/issues/767
                         * @psalm-suppress NoInterfaceProperties
                         */
                        throw new SyntaxException(
                            "Invalid closing tag `{$this->node->name}`, expected `{$asm->getNode()->name}`",
                            $this->token
                        );
                    }

                    $asm->close();
                } elseif (\in_array($this->node->name, self::VOID_TAGS)) {
                    $this->node->void = true;
                    $asm->push($this->node);
                } else {
                    $asm->open($this->node, 'nodes');
                }
                $this->flush();

                break;

            case HTMLGrammar::TYPE_VERBATIM:
                $asm->push($this->parseVerbatim($parser, $token));
                break;

            default:
                if ($asm->getNode() instanceof Mixin || $asm->getNode() instanceof Verbatim) {
                    $node = $this->parseToken($parser, $token);
                    if (\is_string($node)) {
                        $node = new Raw($node, new Parser\Context($token, $parser->getPath()));
                    }

                    $asm->push($node);
                }
        }
    }

    /**
     * Flush open nodes and tokens.
     */
    private function flush(): void
    {
        $this->node = null;
        $this->token = null;
        $this->attr = null;
    }

    private function parseVerbatim(Parser $parser, Token $token): Verbatim
    {
        $verbatim = new Verbatim(new Parser\Context($token, $parser->getPath()));

        if ($token->tokens === []) {
            if ($token->content) {
                $verbatim->nodes[] = $token->content;
            }
        } else {
            /**
             * TODO issue #767
             * @link https://github.com/spiral/framework/issues/767
             * @psalm-suppress InvalidArgument
             */
            $parser->parseTokens(
                new Assembler($verbatim, 'nodes'),
                $token->tokens
            );
        }

        return $verbatim;
    }
}
