<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

final class DirectiveGroup implements DirectiveRendererInterface
{
    /** @var DirectiveRendererInterface[] */
    private $directives = [];

    /**
     * @param array $directives
     */
    public function __construct(array $directives = [])
    {
        $this->directives = $directives;
    }

    /**
     * Add new directive(s) compiler.
     *
     * @param DirectiveRendererInterface $directiveCompiler
     */
    public function addDirective(DirectiveRendererInterface $directiveCompiler): void
    {
        $this->directives[] = $directiveCompiler;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasDirective(string $name): bool
    {
        foreach ($this->directives as $directiveRenderer) {
            if ($directiveRenderer->hasDirective($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Directive $directive
     * @return string|null
     */
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
