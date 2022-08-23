<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Context;

use Spiral\Core\Exception\LogicException;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Node\AttributedInterface;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Transform\Import\ImportInterface;
use Spiral\Stempler\VisitorContext;

/**
 * Manages currently open scope of imports (via nested tags).
 */
final class ImportContext
{
    /** @var VisitorContext */
    private $ctx;

    private function __construct(VisitorContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function add(ImportInterface $import): void
    {
        $node = $this->ctx->getParentNode();
        if (!$node instanceof AttributedInterface) {
            throw new LogicException(\sprintf(
                'Unable to create import on node without attribute storage (%s)',
                \is_object($node) ? \get_class($node) : \gettype($node)
            ));
        }

        $imports = $node->getAttribute(self::class, []);
        $imports[] = $import;
        $node->setAttribute(self::class, $imports);
    }

    /**
     * Resolve imported element template.
     */
    public function resolve(Builder $builder, string $name): ?Template
    {
        foreach ($this->getImports() as $import) {
            $tpl = $import->resolve($builder, $name);
            if ($tpl !== null) {
                return $tpl;
            }
        }

        return null;
    }

    /**
     * Return all imports assigned to the given path.
     *
     * @return ImportInterface[]
     */
    public function getImports(): array
    {
        $imports = [];
        foreach (array_reverse($this->ctx->getScope()) as $node) {
            if ($node instanceof AttributedInterface) {
                foreach ($node->getAttribute(self::class, []) as $import) {
                    $imports[] = $import;
                }
            }
        }

        return $imports;
    }

    public static function on(VisitorContext $ctx): self
    {
        return new self($ctx);
    }
}
