<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Import;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Provides the ability to import block defined in the same template.
 */
final class Inline implements ImportInterface
{
    use ContextTrait;

    /** @var string */
    private $name;

    /** @var array */
    private $nodes;

    /**
     * @param string       $name
     * @param array        $nodes
     * @param Context|null $context
     */
    public function __construct(string $name, array $nodes, Context $context = null)
    {
        $this->name = $name;
        $this->nodes = $nodes;
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Builder $builder, string $name): ?Template
    {
        if ($name !== $this->name) {
            return null;
        }

        $tpl = new Template($this->nodes);
        $tpl->setContext($this->context);

        return $tpl;
    }
}
