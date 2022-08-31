<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

use Spiral\Stempler\Lexer\Grammar\RawGrammar;

/**
 * Tokenize given byte stream into stream of tokens (like real stream, not "array"). Lexer support pluginable
 * grammars.
 */
final class Lexer
{
    /** @var GrammarInterface[] */
    private array $grammars = [];

    /**
     * Attach grammar layer.
     */
    public function addGrammar(GrammarInterface $grammar): int
    {
        $this->grammars[] = $grammar;

        return \count($this->grammars) - 1;
    }

    /**
     * Generate token stream.
     */
    public function parse(StreamInterface $src): \Generator
    {
        $stream = new Buffer($this->generate($src));
        foreach ($this->grammars as $grammar) {
            $stream = new Buffer($this->wrap(clone $grammar, $stream));
        }

        // always group raw bytes into raw tokens
        foreach ($this->wrap(new RawGrammar(), $stream) as $n) {
            yield $n;
        }
    }

    private function wrap(GrammarInterface $grammar, Buffer $stream): \Generator
    {
        foreach ($grammar->parse($stream) as $n) {
            if ($n instanceof Token && $n->grammar === null) {
                $n->grammar = $grammar::class;
            }

            yield $n;
        }
    }

    /**
     * Generate character stream and aggregate grammar results.
     *
     * @return \Generator<array-key, Byte>
     */
    private function generate(StreamInterface $src): \Generator
    {
        while (!$src->isEOI()) {
            yield new Byte($src->getOffset(), $src->peak());
        }
    }
}
