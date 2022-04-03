<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Finalizer;

use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Transform\Merge\Inject\PHPMixin;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Isolates PHP block definitions inside the origin template.
 */
final class IsolatePHPBlocks implements VisitorInterface
{
    // php marcos to inject values into
    private const PHP_MARCO_EXISTS_FUNCTION = 'injected';

    public function __construct(
        private readonly string $path
    ) {
    }

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        if (!$node instanceof PHP || !\str_contains($node->content, self::PHP_MARCO_EXISTS_FUNCTION)) {
            return null;
        }

        if ($node->getContext()->getPath() !== $this->path) {
            return null;
        }

        $exists = new PHPMixin($node->tokens, self::PHP_MARCO_EXISTS_FUNCTION);
        foreach (\array_keys($exists->getBlocks()) as $name) {
            // do not leak to parent template
            $exists->set($name, 'false');
        }

        $node->content = $exists->compile();
        $node->tokens = \token_get_all($node->content);

        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }
}
