<?php

declare(strict_types=1);

namespace Spiral\Stempler\Exception;

use Spiral\Stempler\Lexer\Token;

/**
 * Syntax exceptions can be intercepted at Builder level to properly associate
 * filepath.
 */
class SyntaxException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly Token $token
    ) {
        $message = \sprintf('%s at offset %s', $message, $token->offset);
        parent::__construct($message, 0, null);
    }

    public function getToken(): Token
    {
        return $this->token;
    }
}
