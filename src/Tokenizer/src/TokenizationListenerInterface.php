<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

interface TokenizationListenerInterface
{
    public function listen(\ReflectionClass $class): void;

    public function finalize(): void;
}
