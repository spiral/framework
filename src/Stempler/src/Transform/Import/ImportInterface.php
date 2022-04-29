<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Import;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser\Context;

interface ImportInterface
{
    public function getContext(): ?Context;

    /**
     * Resolve template by it's name or return null if import does not work
     * for the given name.
     */
    public function resolve(Builder $builder, string $name): ?Template;
}
