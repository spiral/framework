<?php

declare(strict_types=1);

namespace Spiral\Console\Signature;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class Result
{
    public function __construct(
        /** @var non-empty-string*/
        public readonly string $name,
        /** @var InputArgument[] */
        public readonly array $arguments = [],
        /** @var InputOption[] */
        public readonly array $options = [],
    ) {
    }
}
