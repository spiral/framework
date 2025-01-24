<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Grammar;

use Spiral\Stempler\Lexer\Grammar\PHPGrammar;
use Spiral\Stempler\Lexer\Token;

class PHPTest extends BaseTestCase
{
    protected const GRAMMARS = [PHPGrammar::class];

    public function testRaw(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, 'raw body'),
            ],
            ('raw body'),
        );
    }

    public function testPHP(): void
    {
        $this->assertTokens(
            [
                new Token(PHPGrammar::TYPE_CODE, 0, '<?php echo "raw body"?>'),
            ],
            ('<?php echo "raw body"?>'),
        );
    }

    public function testPHPComma(): void
    {
        $this->assertTokens(
            [
                new Token(PHPGrammar::TYPE_CODE, 0, '<?php echo "raw body", 123 ?>'),
            ],
            ('<?php echo "raw body", 123 ?>'),
        );
    }

    public function testPHPShort(): void
    {
        $this->assertTokens(
            [
                new Token(PHPGrammar::TYPE_CODE, 0, '<?="raw body"?>'),
            ],
            ('<?="raw body"?>'),
        );
    }

    public function testDoublePHP(): void
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, 'start'),
                new Token(PHPGrammar::TYPE_CODE, 5, '<?="1"?>'),
                new Token(Token::TYPE_RAW, 13, 'middle'),
                new Token(PHPGrammar::TYPE_CODE, 19, '<?="2"?>'),
                new Token(Token::TYPE_RAW, 27, 'end'),
            ],
            ('start<?="1"?>middle<?="2"?>end'),
        );
    }
}
