<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

final class DirectiveGroup implements DirectiveRendererInterface
{
    /**
     * @param \Spiral\Stempler\Directive\DirectiveRendererInterface[] $directives
     */
    public function __construct(
        private array $directives = []
    ) {
    }

    /**
     * Add new directive(s) compiler.
     */
    public function addDirective(DirectiveRendererInterface $directiveCompiler): void
    {
        $this->directives[] = $directiveCompiler;
    }

    public function hasDirective(string $name): bool
    {
        foreach ($this->directives as $directiveRenderer) {
            if ($directiveRenderer->hasDirective($name)) {
                return true;
            }
        }

        return false;
    }

    public function render(Directive $directive): ?string
    {
        foreach ($this->directives as $directiveRenderer) {
            if ($directiveRenderer->hasDirective($directive->name)) {
                return $directiveRenderer->render($directive);
            }
        }

        return null;
    }
}
