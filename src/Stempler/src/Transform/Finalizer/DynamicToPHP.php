<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Finalizer;

use Spiral\Stempler\Compiler\Renderer\DynamicRenderer;
use Spiral\Stempler\Directive\DirectiveRendererInterface;
use Spiral\Stempler\Exception\DirectiveException;
use Spiral\Stempler\Node\Dynamic\Directive;
use Spiral\Stempler\Node\Dynamic\Output;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\HTML\Verbatim;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Transform\Merge\ExtendsParent;
use Spiral\Stempler\Traverser;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Compile all directives and output blocks into PHP equivalent with context aware escaping (when needed).
 */
final class DynamicToPHP implements VisitorInterface
{
    // default output filter
    public const DEFAULT_FILTER = DynamicRenderer::DEFAULT_FILTER;

    /** @var string */
    private $defaultFilter;

    /** @var DirectiveRendererInterface[] */
    private $directives;

    /** @var Traverser */
    private $traverser;

    public function __construct(string $defaultFilter = self::DEFAULT_FILTER, array $directives = [])
    {
        $this->defaultFilter = $defaultFilter;
        $this->directives = $directives;

        $this->traverser = new Traverser();
        $this->traverser->addVisitor($this);
    }

    /**
     * Add new directive(s) compiler.
     */
    public function addDirective(DirectiveRendererInterface $directiveCompiler): void
    {
        $this->directives[] = $directiveCompiler;
    }

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx): void
    {
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx)
    {
        if ($node instanceof Output) {
            return $this->output($node, $ctx);
        }

        if ($node instanceof Directive) {
            return $this->directive($node);
        }

        if ($node instanceof Template) {
            $extendsTag = $node->getAttribute(ExtendsParent::class);
            if ($extendsTag instanceof Tag) {
                $extendsTag->attrs = $this->traverser->traverse($extendsTag->attrs);
            }
        }

        return null;
    }

    private function directive(Directive $node): PHP
    {
        foreach ($this->directives as $renderer) {
            $result = $renderer->render($node);
            if ($result !== null) {
                return new PHP($result, token_get_all($result), $node->getContext());
            }
        }

        throw new DirectiveException(
            "Undefined directive `{$node->name}`",
            $node->getContext()
        );
    }

    private function output(Output $node, VisitorContext $ctx): PHP
    {
        /*
         * In future this method can support context aware escaping based on tag location.
         */

        if ($node->rawOutput) {
            $result = sprintf('<?php echo %s; ?>', trim($node->body));
        } else {
            $filter = $node->filter ?? $this->getFilterContext($ctx);

            $result = sprintf("<?php echo {$filter}; ?>", trim($node->body));
        }

        return new PHP(
            $result,
            token_get_all($result),
            $node->getContext()->withValue(PHP::ORIGINAL_BODY, trim($node->body))
        );
    }

    private function getFilterContext(VisitorContext $ctx): string
    {
        // only "interesting" nodes
        $context = [];

        foreach (array_reverse($ctx->getScope()) as $node) {
            if ($node instanceof Attr || $node instanceof Tag || $node instanceof Verbatim) {
                $context[] = $node;
            }

            if (count($context) === 2) {
                break;
            }
        }

        if (count($context) !== 2) {
            return $this->defaultFilter;
        }

        // php {{ }} in javascript code (variable passing), use {! !} to bypass the filter
        if ($context[0] instanceof Verbatim && $context[1] instanceof Tag && $context[1]->name === 'script') {
            return sprintf(
                'json_encode(%s, %s, %s)',
                '%s',
                'JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT',
                '512'
            );
        }

        // in on* and other attributes
        if ($context[0] instanceof Verbatim && $context[1] instanceof Attr && $context[1]->name !== 'style') {
            return sprintf("'%s', %s, '%s'", '&quot;', $this->defaultFilter, '&quot;');
        }

        return $this->defaultFilter;
    }
}
