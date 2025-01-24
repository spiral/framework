<?php

declare(strict_types=1);

namespace Spiral\App\Tokenizer;

use Spiral\Tokenizer\TokenizationListenerInterface;

final class InvalidListener implements TokenizationListenerInterface
{
    public function listen(\ReflectionClass $class): void {}

    public function finalize(): void {}
}
