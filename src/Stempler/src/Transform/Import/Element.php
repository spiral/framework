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
 * Import one element by it's name.
 */
final class Element implements ImportInterface
{
    use ContextTrait;

    /** @var string */
    private $path;

    /** @var string */
    private $alias;

    /**
     * @param string       $path
     * @param string|null  $alias
     * @param Context|null $context
     */
    public function __construct(string $path, string $alias = null, Context $context = null)
    {
        $this->path = $path;
        $this->alias = $alias ?? $path;

        if ($alias === null && strrpos($this->alias, '/') !== false) {
            $this->alias = substr($this->alias, strrpos($this->alias, '/') + 1);
        }

        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Builder $builder, string $name): ?Template
    {
        if ($this->alias !== $name) {
            return null;
        }

        return $builder->load($this->path);
    }
}
