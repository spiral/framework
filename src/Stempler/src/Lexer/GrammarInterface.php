<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

interface GrammarInterface
{
    /**
     * Generate stream of tokens or pass generation to overlay grammar.
     *
     * @return \Generator<array-key, Byte|Token|null>
     */
    public function parse(Buffer $src): \Generator;

    /**
     * Return unique token name for the given grammar.
     */
    public static function tokenName(int $token): string;
}
