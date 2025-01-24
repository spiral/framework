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
        ?Context $context = null,
    ) {
        $this->prefix = $prefix ?? \substr($path, \strrpos($path, '/') + 1);
        $this->context = $context;
    }

    public function resolve(Builder $builder, string $name): ?Template
    {
        if (!TagHelper::hasPrefix($name, $this->prefix)) {
            return null;
        }

        $path = TagHelper::stripPrefix($name, $this->prefix);
        $path = \str_replace('.', '/', $path);

        return $builder->load("{$this->path}/{$path}");
    }
}
