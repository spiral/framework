<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views\Context;

use Spiral\Views\DependencyInterface;

/**
 * Fixed value dependency.
 */
final class ValueDependency implements DependencyInterface
{
    /** @var string */
    private $name;

    /** @var mixed */
    private $value;

    /** @var array */
    private $variants;

    /**
     * @param mixed  $value
     */
    public function __construct(string $name, $value, array $variants = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->variants = $variants ?? [$value];
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function getVariants(): array
    {
        return $this->variants;
    }
}
