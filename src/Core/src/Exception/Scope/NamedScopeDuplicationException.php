<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Scope;

use Spiral\Core\Exception\Container\ContainerException;

class NamedScopeDuplicationException extends ContainerException
{
    public function __construct(
        protected string $scope,
    ) {
        parent::__construct(
            "Error on a scope allocation with the name `{$scope}`. A scope with the same name already exists."
        );
    }

    public function getScope(): string
    {
        return $this->scope;
    }
}
