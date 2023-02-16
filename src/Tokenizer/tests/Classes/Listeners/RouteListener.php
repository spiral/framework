<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Listeners;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetMethod;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\Attribute\TargetClass;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(WithTargetMethod::class)]
#[TargetClass(WithTargetMethod::class, scope: 'routes')]
final class RouteListener implements TokenizationListenerInterface
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
