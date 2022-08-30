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
 * Load directory, supports . as directory separator.
 */
final class Directory implements ImportInterface
{
    use ContextTrait;

    /** @var string */
    public $path;

    /** @var string */
    public $prefix;

    /**
     * @param Context|null $context
     */
    public function __construct(string $path, string $prefix, Context $context = null)
    {
        $this->path = $path;
        $this->prefix = $prefix ?? substr($path, strrpos($path, '/') + 1);
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Builder $builder, string $name): ?Template
    {
        $path = substr($name, strlen($this->prefix) + 1);
        $path = str_replace('.', DIRECTORY_SEPARATOR, $path);

        return $builder->load($this->path . DIRECTORY_SEPARATOR . $path);
    }
}
