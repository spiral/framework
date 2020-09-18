<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge;

use Spiral\Core\Exception\LogicException;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Exception\ExtendsException;
use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Node\AttributedInterface;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Transform\Merger;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Extends parent template using blocks defined withing template scope (same level
 * as extends: tag).
 */
final class ExtendsParent implements VisitorInterface
{
    /** @var string */
    private $extendsKeyword = 'extends';

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
        if ($node instanceof Tag && strpos($node->name, $this->extendsKeyword) === 0) {
            return self::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx)
    {
        if ($node instanceof Tag && strpos($node->name, $this->extendsKeyword) === 0) {
            $parent = $ctx->getParentNode();
            if (!$parent instanceof AttributedInterface) {
                throw new LogicException(sprintf(
                    'Unable to extend non attributable node (%s)',
                    is_object($node) ? get_class($node) : gettype($node)
                ));
            }

            $parent->setAttribute(self::class, $node);

            return self::REMOVE_NODE;
        }

        // extend current node
        if ($node instanceof AttributedInterface && $node->getAttribute(self::class) !== null) {
            /** @var Tag $extends */
            $extends = $node->getAttribute(self::class);

            foreach ($node->nodes as $child) {
                $extends->nodes[] = $child;
            }

            $path = 'undefined';
            try {
                $path = $this->getPath($extends);

                return $this->merger->merge($this->builder->load($path), $extends);
            } catch (\Throwable $e) {
                throw new ExtendsException(
                    "Unable to extend parent `{$path}`",
                    $extends->getContext(),
                    $e
                );
            }
        }

        return null;
    }

    /**
     * @param Tag $tag
     * @return string
     */
    private function getPath(Tag $tag): string
    {
        if (strpos($tag->name, $this->extendsKeyword . ':') === 0) {
            $name = substr($tag->name, strlen($this->extendsKeyword) + 1);

            return str_replace(['.'], DIRECTORY_SEPARATOR, $name);
        }

        foreach ($tag->attrs as $attr) {
            if ($attr->name === 'path' && is_string($attr->value)) {
                return trim($attr->value, '\'"');
            }
        }

        // might be non existed
        throw new SyntaxException(
            'Unable to extend parent without specified path',
            $tag->getContext()->getToken()
        );
    }
}
