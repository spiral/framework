<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node\HTML;

use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Represents single node/tag attribute and it's value.
 */
final class Attr implements NodeInterface
{
    use ContextTrait;

    /** @var Mixin|string */
    public $name;

    /** @var Mixin|Nil|string */
    public $value;

    /**
     * @param Mixin|string     $name
     * @param Mixin|Nil|string $value
     * @param Context          $context
     */
    public function __construct($name, $value, Context $context = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield 'name' => $this->name;
        yield 'value' => $this->value;
    }
}
