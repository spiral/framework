<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx): void
    {
        if (
            !$node instanceof PHP
            || strpos($node->content, self::PHP_MARCO_EXISTS_FUNCTION) === false
        ) {
            return;
        }

        if ($node->getContext()->getPath() !== $this->path) {
            return;
        }

        $exists = new PHPMixin($node->tokens, self::PHP_MARCO_EXISTS_FUNCTION);
        foreach ($exists->getBlocks() as $name => $_) {
            // do not leak to parent template
            $exists->set($name, 'false');
        }

        $node->content = $exists->compile();
        $node->tokens = token_get_all($node->content);
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx): void
    {
    }
}
