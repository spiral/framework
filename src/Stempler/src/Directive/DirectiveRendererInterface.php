<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

/**
 * Compiles one or multiple directives.
 */
interface DirectiveRendererInterface
{
    public function hasDirective(string $name): bool;

    public function render(Directive $directive): ?string;
}
