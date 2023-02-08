<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Scope;

use Spiral\Core\Exception\Container\ContainerException;

/**
 * @internal
 */
abstract class ScopeException extends ContainerException
{
    public function __construct(
        protected ?string $scope,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            $message,
            $code,
            $previous,
        );
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
