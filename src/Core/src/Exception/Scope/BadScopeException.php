<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Scope;

use Spiral\Core\Exception\Container\ContainerException;

class BadScopeException extends ContainerException
{
    public function __construct(
        protected string $scope,
        protected string $className,
    ) {
        parent::__construct(
            \sprintf('Class `%s` can be resolved only in `%s` scope.', $className, $scope),
        );
    }

    public function getScope(): string
    {
        return $this->scope;
    }
}
