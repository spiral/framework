<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Directive;

use Spiral\Stempler\Directive\DirectiveGroup;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Lexer\StringStream;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;

class OnlyKnownTest extends BaseTest
{
    protected const DIRECTIVES = [
        LoopDirective::class
    ];

    public function testForeachEndForeach(): void
    {
        $doc = $this->parse('@foreach($users as $u) {{ $u->name }} @endforeach @hello after');

        $this->assertSame(
            '<?php foreach($users as $u): ?> <?php echo htmlspecialchars'
            . "((string) \$u->name, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8'); ?> <?php endforeach; ?> @hello after",
            $this->compile($doc)
        );
    }

    /**
     * @param string $string
     * @return Template
     */
    protected function parse(string $string): Template
    {
        $parser = new Parser();

        $directives = new DirectiveGroup();
        $directives->addDirective(new LoopDirective());

        $parser->addSyntax(new DynamicGrammar($directives), new Parser\Syntax\DynamicSyntax());
        $parser->addSyntax(new HTMLGrammar(), new HTMLSyntax());

        return $parser->parse(new StringStream($string));
    }
}
