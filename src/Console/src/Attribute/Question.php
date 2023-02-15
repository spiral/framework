<?php

declare(strict_types=1);

namespace Spiral\Console\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
#[NamedArgumentConstructor]
final class Question
{
    public function __construct(
        public readonly string $question,
        public readonly ?string $argument = null
    ) {
    }
}
