<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\TokenizationListenerInterface;

/**
 * @internal
 */
final class ListenerDefinition
{
    /**
     * @param class-string<TokenizationListenerInterface> $listenerClass
     */
    public function __construct(
        public readonly string $listenerClass,
        public readonly \ReflectionClass $target,
        public readonly ?string $scope = null,
        public readonly ?\Attribute $attribute = null,
    ) {
    }

    public function getHash(): string
    {
        return \md5($this->target->getName() . $this->scope);
    }
}
