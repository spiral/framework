<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Compiler;

use Spiral\Stempler\Parser\Context;

/**
 * Describes the location of a source line in relation to external path.
 */
final class Location
{
    /** @var string */
    public $path;

    /** @var int */
    public $offset;

    /** @var string|null @internal */
    public $grammar;

    /** @var Location|null */
    public $parent;

    /**
     * @param string        $path
     * @param int           $offset
     * @param Location|null $parent
     * @param string|null   $grammar
     */
    public function __construct(string $path, int $offset, string $grammar = null, Location $parent = null)
    {
        $this->path = $path;
        $this->offset = $offset;
        $this->grammar = $grammar;
        $this->parent = $parent;
    }

    /**
     * @param Context       $context
     * @param Location|null $parent
     * @return Location
     */
    public static function fromContext(Context $context, Location $parent = null): Location
    {
        return new self(
            $context->getPath(),
            $context->getToken()->offset,
            $context->getToken()->grammar,
            $parent
        );
    }
}
