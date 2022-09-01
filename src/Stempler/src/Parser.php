<?php

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Exception\ParserException;
use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Lexer\Grammar\RawGrammar;
use Spiral\Stempler\Lexer\GrammarInterface;
use Spiral\Stempler\Lexer\Lexer;
use Spiral\Stempler\Lexer\StreamInterface;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser\Assembler;
use Spiral\Stempler\Parser\Context;
use Spiral\Stempler\Parser\Syntax\RawSyntax;
use Spiral\Stempler\Parser\SyntaxInterface;

/**
 * Module content parser with configurable grammars and syntaxes.
 */
final class Parser
{
    private Lexer $lexer;

    private ?string $path = null;

    /** @var SyntaxInterface[] */
    private array $syntax = [];

    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->syntax = [RawGrammar::class => new RawSyntax()];
    }

    /**
     * Associate template path with Parser (source-map).
     */
    public function withPath(string $path = null): self
    {
        $parser = clone $this;
        $parser->path = $path;
        $parser->lexer = clone $this->lexer;

        foreach ($parser->syntax as $grammar => $stx) {
            $parser->syntax[$grammar] = clone $stx;
        }

        return $parser;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Add new parser grammar and syntax (registration order matter!).
     */
    public function addSyntax(GrammarInterface $grammar, SyntaxInterface $generator): void
    {
        $this->lexer->addGrammar($grammar);
        $this->syntax[$grammar::class] = $generator;
    }

    /**
     * @throws ParserException
     */
    public function parse(StreamInterface $stream): Template
    {
        $template = new Template();

        try {
            /**
             * TODO issue #767
             * @link https://github.com/spiral/framework/issues/767
             * @psalm-suppress InvalidArgument
             */
            $this->parseTokens(
                new Assembler($template, 'nodes'),
                $this->lexer->parse($stream)
            );
        } catch (SyntaxException $e) {
            throw new ParserException(
                $e->getMessage(),
                new Context($e->getToken(), $this->getPath()),
                $e
            );
        }

        return $template;
    }

    /**
     * @throws SyntaxException
     */
    public function parseTokens(Assembler $asm, iterable $tokens): void
    {
        $node = $asm->getNode();

        $syntax = [];
        foreach ($this->syntax as $grammar => $stx) {
            $syntax[$grammar] = clone $stx;
        }

        foreach ($tokens as $token) {
            if (!isset($syntax[$token->grammar])) {
                throw new SyntaxException('Undefined token', $token);
            }

            $syntax[$token->grammar]->handle($this, $asm, $token);
        }

        if ($asm->getNode() !== $node) {
            throw new SyntaxException(
                'Invalid node hierarchy, unclosed ' . $asm->getStackPath(),
                $asm->getNode()->getContext()->getToken()
            );
        }
    }
}
