<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Listeners;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetConstant;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(WithTargetConstant::class)]
class ConstantListener implements TokenizationListenerInterface
{
    public function listen(\ReflectionClass $class): void
    {
        // TODO: Implement listen() method.
    }

    public function finalize(): void
    {
        // TODO: Implement finalize() method.
    }
}
