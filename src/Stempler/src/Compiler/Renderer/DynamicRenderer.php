<?php

declare(strict_types=1);

namespace Spiral\Stempler\Compiler\Renderer;

use Spiral\Stempler\Compiler;
use Spiral\Stempler\Compiler\RendererInterface;
use Spiral\Stempler\Compiler\Result;
use Spiral\Stempler\Directive\DirectiveRendererInterface;
use Spiral\Stempler\Exception\DirectiveException;
use Spiral\Stempler\Node\Dynamic\Directive;
use Spiral\Stempler\Node\Dynamic\Output;
use Spiral\Stempler\Node\NodeInterface;

final class DynamicRenderer implements RendererInterface
{
    // default output filter
    public const DEFAULT_FILTER = "htmlspecialchars((string) (%s), ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8')";

    public function __construct(
        private readonly ?DirectiveRendererInterface $directiveRenderer = null,
        private readonly string $defaultFilter = self::DEFAULT_FILTER
    ) {
    }

    public function render(Compiler $compiler, Result $result, NodeInterface $node): bool
    {
        switch (true) {
            case $node instanceof Output:
                $this->output($result, $node);
                return true;
            case $node instanceof Directive:
                $this->directive($result, $node);
                return true;
            default:
                return false;
        }
    }

    /**
     * @throws DirectiveException
     */
    private function directive(Result $source, Directive $directive): void
    {
        if ($this->directiveRenderer !== null) {
            $result = $this->directiveRenderer->render($directive);
            if ($result !== null) {
                $source->push($result, $directive->getContext());
                return;
            }
        }

        throw new DirectiveException(
            \sprintf('Undefined directive `%s`', $directive->name),
            $directive->getContext()
        );
    }

    private function output(Result $source, Output $output): void
    {
        if ($output->rawOutput) {
            $source->push(\sprintf('<?php echo %s; ?>', \trim($output->body)), $output->getContext());
            return;
        }

        $filter = $output->filter ?? $this->defaultFilter;

        $source->push(
            \sprintf(\sprintf('<?php echo %s; ?>', $filter), \trim($output->body)),
            $output->getContext()
        );
    }
}
