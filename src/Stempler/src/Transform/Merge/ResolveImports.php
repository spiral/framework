<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Exception\ImportException;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Transform\Context\ImportContext;
use Spiral\Stempler\Transform\Import\Bundle;
use Spiral\Stempler\Transform\Import\Directory;
use Spiral\Stempler\Transform\Import\Element;
use Spiral\Stempler\Transform\Import\ImportInterface;
use Spiral\Stempler\Transform\Import\Inline;
use Spiral\Stempler\Transform\Merger;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Resolves inline imports and use tags.
 */
final class ResolveImports implements VisitorInterface
{
    /** @var string */
    private $useKeyword = 'use:';

    /** @var Builder */
    private $builder;

    /** @var Merger */
    private $merger;

    /**
     * @param Builder $builder
     * @param Merger  $merger
     */
    public function __construct(Builder $builder, Merger $merger = null)
    {
        $this->builder = $builder;
        $this->merger = $merger ?? new Merger();
    }

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx)
    {
        if ($node instanceof Tag && strpos($node->name, $this->useKeyword) === 0) {
            return self::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx)
    {
        if (!$node instanceof Tag) {
            return null;
        }

        $importCtx = ImportContext::on($ctx);

        // import definition
        if (strpos($node->name, $this->useKeyword) === 0) {
            $importCtx->add($this->makeImport($node));

            return self::REMOVE_NODE;
        }

        // imported tag
        try {
            $import = $importCtx->resolve($this->builder, $node->name);
        } catch (\Throwable $e) {
            throw new ImportException(
                "Unable to resolve import `{$node->name}`",
                $node->getContext(),
                $e
            );
        }

        if ($import !== null) {
            $node = $this->merger->merge($import, $node);

            return $this->merger->isolateNodes($node, $import->getContext()->getPath());
        }

        return null;
    }

    /**
     * Create import definition (aka "use").
     *
     * @param Tag $tag
     * @return ImportInterface|null
     */
    private function makeImport(Tag $tag): ImportInterface
    {
        $options = [];
        foreach ($tag->attrs as $attr) {
            if (is_string($attr->value)) {
                $options[$attr->name] = trim($attr->value, '\'"');
            }
        }

        switch (strtolower($tag->name)) {
            case 'use':
            case 'use:element':
                $this->assertHasOption('path', $options, $tag);

                return new Element(
                    $options['path'],
                    $options['as'] ?? $options['alias'] ?? null,
                    $tag->getContext()
                );

            case 'use:dir':
                $this->assertHasOption('dir', $options, $tag);
                $this->assertHasOption('ns', $options, $tag);

                return new Directory(
                    $options['dir'],
                    $options['ns'],
                    $tag->getContext()
                );

            case 'use:bundle':
                $this->assertHasOption('path', $options, $tag);

                return new Bundle(
                    $options['path'],
                    $options['ns'] ?? null,
                    $tag->getContext()
                );

            case 'use:inline':
                $this->assertHasOption('name', $options, $tag);

                return new Inline(
                    $options['name'],
                    $tag->nodes,
                    $tag->getContext()
                );

            default:
                return null;
        }
    }

    /**
     * @param string $option
     * @param array  $options
     * @param Tag    $tag
     */
    private function assertHasOption(string $option, array $options, Tag $tag): void
    {
        if (!isset($options[$option])) {
            throw new ImportException("Missing `{$option}` option", $tag->getContext());
        }
    }
}
