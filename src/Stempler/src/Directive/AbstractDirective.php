<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

/**
 * Automatically invokes methods associated with directive name.
 */
abstract class AbstractDirective implements DirectiveRendererInterface
{
    private readonly \ReflectionObject $r;

    /**
     * AbstractDirective constructor.
     */
    public function __construct()
    {
        $this->r = new \ReflectionObject($this);
    }

    public function hasDirective(string $name): bool
    {
        return $this->r->hasMethod('render' . \ucfirst($name));
    }

    public function render(Directive $directive): ?string
    {
        if (!$this->hasDirective($directive->name)) {
            return null;
        }

        return \call_user_func([$this, 'render' . \ucfirst($directive->name)], $directive);
    }
}
