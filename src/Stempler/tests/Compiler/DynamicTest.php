<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Compiler;

use Spiral\Stempler\Compiler\Renderer\CoreRenderer;
use Spiral\Stempler\Compiler\Renderer\DynamicRenderer;
use Spiral\Stempler\Compiler\Renderer\HTMLRenderer;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Parser\Syntax\DynamicSyntax;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;

class DynamicTest extends BaseTest
{
    protected const RENDERS = [
        CoreRenderer::class,
        HTMLRenderer::class,
        DynamicRenderer::class,
    ];

    protected const GRAMMARS = [
        DynamicGrammar::class => DynamicSyntax::class,
        HTMLGrammar::class    => HTMLSyntax::class
    ];

    public function testOutput(): void
    {
        $doc = $this->parse('{{ $name }}');

        $this->assertSame(
            "<?php echo htmlspecialchars(\$name, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8'); ?>",
            $this->compile($doc)
        );
    }

    public function testOutputEscapeOptions(): void
    {
        $doc = $this->parse('{{ $name }}');

        $doc->nodes[0]->filter = 'e(%s)';

        $this->assertSame(
            '<?php echo e($name); ?>',
            $this->compile($doc)
        );
    }
}
