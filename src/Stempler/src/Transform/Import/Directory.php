<?php

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

    public ?string $prefix;

    public function __construct(
        public string $path,
        ?string $prefix,
        Context $context = null
    ) {
        $this->prefix = $prefix ?? \substr($path, \strrpos($path, '/') + 1);
        $this->context = $context;
    }

    public function resolve(Builder $builder, string $name): ?Template
    {
        $path = \substr($name, \strlen((string) $this->prefix) + 1);
        $path = \str_replace('.', DIRECTORY_SEPARATOR, $path);

        return $builder->load($this->path . DIRECTORY_SEPARATOR . $path);
    }
}
