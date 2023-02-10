<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\TokenizationListenerInterface;

/**
 * @internal
 */
final class ListenerInvoker
{
    public function invoke(TokenizationListenerInterface $listener, iterable $classes): void
    {
        foreach ($classes as $class) {
            $listener->listen($class);
        }

        $listener->finalize();
    }
}
