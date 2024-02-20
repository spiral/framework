<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * @internal
 */
final class DeprecationProxy extends Proxy
{
    /**
     * @param class-string $interface
     */
    public function __construct(
        string $interface,
        bool $singleton = false,
        private readonly string|\BackedEnum|null $scope = null,
        private readonly ?string $version = null,
        private readonly ?string $message = null,
    ) {
        if (($scope === null || $version === null) && $message === null) {
            throw new \InvalidArgumentException('Scope and version or custom message must be provided.');
        }

        parent::__construct($interface, $singleton);
    }

    /**
     * @return class-string
     */
    public function getInterface(): string
    {
        $message = $this->message ?? \sprintf(
            'Using `%s` outside of the `%s` scope is deprecated and will be impossible in version %s.',
            $this->interface,
            $this->scope instanceof \BackedEnum ? $this->scope->value : $this->scope,
            $this->version
        );

        @trigger_error($message, \E_USER_DEPRECATED);

        return parent::getInterface();
    }
}
