<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge;

use Spiral\Core\Exception\LogicException;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Exception\ExtendsException;
use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Node\AttributedInterface;
use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\HTML\Verbatim;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Transform\Merger;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Extends parent template using blocks defined withing template scope (same level
 * as extends: tag).
 */
final class ExtendsParent implements VisitorInterface
{
    private string $extendsKeyword = 'extends';

    public function __construct(
        private readonly Builder $builder,
        private readonly Merger $merger = new Merger()
    ) {
    }

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        if ($node instanceof Tag && \str_starts_with($node->name, $this->extendsKeyword)) {
            return self::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        if ($node instanceof Tag && \str_starts_with($node->name, $this->extendsKeyword)) {
            $parent = $ctx->getParentNode();
            if (!$parent instanceof AttributedInterface) {
                throw new LogicException(\sprintf(
                    'Unable to extend non attributable node (%s)',
                    \get_debug_type($node)
                ));
            }

            $parent->setAttribute(self::class, $node);

            return self::REMOVE_NODE;
        }

        // extend current node
        /** @psalm-var Template|Block|Verbatim|Tag $node */
        if ($node instanceof AttributedInterface && $node->getAttribute(self::class) !== null) {
            /** @var Tag $extends */
            $extends = $node->getAttribute(self::class);

            foreach ($node->nodes as $child) {
                /**
                 * TODO issue #767
                 * @link https://github.com/spiral/framework/issues/767
                 * @psalm-suppress InvalidPropertyAssignmentValue
                 */
                $extends->nodes[] = $child;
            }

            $path = 'undefined';
            try {
                $path = $this->getPath($extends);

                return $this->merger->merge($this->builder->load($path), $extends);
            } catch (\Throwable $e) {
                throw new ExtendsException(
                    \sprintf('Unable to extend parent `%s`', $path),
                    $extends->getContext(),
                    $e
                );
            }
        }

        return null;
    }

    private function getPath(Tag $tag): string
    {
        if (\str_starts_with($tag->name, $this->extendsKeyword . ':')) {
            $name = \substr($tag->name, \strlen($this->extendsKeyword) + 1);

            return \str_replace(['.'], DIRECTORY_SEPARATOR, $name);
        }

        foreach ($tag->attrs as $attr) {
            if ($attr->name === 'path' && \is_string($attr->value)) {
                return \trim($attr->value, '\'"');
            }
        }

        // might be non existed
        throw new SyntaxException(
            'Unable to extend parent without specified path',
            $tag->getContext()->getToken()
        );
    }
}
