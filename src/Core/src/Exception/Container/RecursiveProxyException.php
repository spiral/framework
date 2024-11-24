<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Container;

/**
 * Recursion can occur due to improper container configuration or
 * an unplanned exit from the scope by the execution thread.
 */
class RecursiveProxyException extends ContainerException
{
    public function __construct(
        public readonly string $alias,
        public readonly ?string $bindingScope = null,
        public readonly ?array $callingScope = null,
    ) {
        $message = "Recursive proxy detected for `$alias`.";
        $bindingScope === null or $message .= "\nBinding scope: `$bindingScope`.";
        $callingScope === null or $message .= "\nCalling scope: `" . \implode('.', $callingScope) . '`.';
        parent::__construct($message);
    }
}
