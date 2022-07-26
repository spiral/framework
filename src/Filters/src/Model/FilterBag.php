<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

use Spiral\Models\AbstractEntity;

final class FilterBag
{
    public function __construct(
        public readonly FilterInterface $filter,
        public readonly AbstractEntity $entity,
        public readonly array $schema = [],
        public readonly array $errors = [],
    ) {
    }
}
